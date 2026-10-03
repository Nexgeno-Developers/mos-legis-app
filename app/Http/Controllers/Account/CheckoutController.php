<?php

namespace App\Http\Controllers\Account;

use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Enums\PlagiarismCheckType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AddressRequest;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\PlagiarismCheck;
use App\Services\Manuscripts\FeeCalculator;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentService;
use App\Services\Payments\SimulatedGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Checkout for pre-screening, publication and standalone plagiarism fees:
 * billing address (tax by country) → pending payment + gateway order → Razorpay
 * checkout → signature-verified callback. Webhooks settle payments too (idempotent).
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly FeeCalculator $fees,
        private readonly PaymentService $payments,
        private readonly PaymentGateway $gateway,
    ) {}

    public function submission(Request $request, ManuscriptSubmission $submission, PaymentPurpose $purpose): View|RedirectResponse
    {
        Gate::authorize('pay', $submission);

        if ($redirect = $this->guardSubmission($submission, $purpose)) {
            return $redirect;
        }

        return $this->summary($request, $submission, $purpose, $this->amountFor($submission, $purpose));
    }

    public function plagiarismCheck(Request $request, PlagiarismCheck $check): View|RedirectResponse
    {
        abort_unless($check->user_id === $request->user()->id && $check->check_type === PlagiarismCheckType::Standalone, 403);

        if ($check->payment_id) {
            return redirect()->route('account.plagiarism-checks.show', $check)->with('status', 'This check has already been paid.');
        }

        return $this->summary($request, $check, PaymentPurpose::PlagiarismCheck, $this->fees->standaloneCheckFee());
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $data = $request->validate([
            'payable_type' => ['required', 'in:manuscript_submissions,plagiarism_checks'],
            'payable_id' => ['required', 'integer'],
            'purpose' => ['required', Rule::enum(PaymentPurpose::class)],
        ]);
        $purpose = PaymentPurpose::from($data['purpose']);
        $user = $request->user();

        if ($data['payable_type'] === 'manuscript_submissions') {
            $payable = ManuscriptSubmission::findOrFail($data['payable_id']);
            Gate::authorize('pay', $payable);
            if ($redirect = $this->guardSubmission($payable, $purpose)) {
                return $redirect;
            }
            $amount = $this->amountFor($payable, $purpose);
        } else {
            $payable = PlagiarismCheck::findOrFail($data['payable_id']);
            abort_unless($payable->user_id === $user->id && ! $payable->payment_id && $purpose === PaymentPurpose::PlagiarismCheck, 403);
            $amount = $this->fees->standaloneCheckFee();
        }

        $address = $user->address()->updateOrCreate([], $request->safe()->only(array_keys(AddressRequest::addressRules())));
        $payment = $this->payments->createPending($user, $payable, $purpose, $amount, $address);
        $payment->forceFill(['gateway_order_id' => $this->gateway->createOrder($payment)])->save();

        return redirect()->route('account.payments.pay', $payment);
    }

    public function pay(Request $request, Payment $payment): View|RedirectResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        if ($payment->isPaid()) {
            return redirect()->to($this->returnUrl($payment))->with('status', 'This payment is already complete.');
        }

        return view('account.checkout.pay', [
            'payment' => $payment->load('payable'),
            'gateway' => $this->gateway->name(),
            'options' => $this->gateway->checkoutOptions($payment),
        ]);
    }

    /** Razorpay handler posts here after a successful checkout. */
    public function callback(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $payment = Payment::where('gateway_order_id', $data['razorpay_order_id'])->where('user_id', $request->user()->id)->firstOrFail();

        if (! $this->gateway->verifyPayment($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature'])) {
            $this->payments->markFailed($payment, 'Signature verification failed');

            return redirect()->route('account.payments.pay', $payment)->with('error', 'We could not verify the payment. If money was debited it will be reconciled automatically.');
        }

        $details = $this->gateway->paymentDetails($data['razorpay_payment_id']);
        $this->payments->markPaid($payment, $data['razorpay_payment_id'], $details['method'], $details['details']);

        return redirect()->to($this->returnUrl($payment))->with('success', 'Payment received — thank you. Your invoice is available under Payments & Invoices.');
    }

    /** Local/testing only: settle the payment without a gateway. */
    public function simulate(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($this->gateway instanceof SimulatedGateway && ! app()->isProduction(), 404);
        abort_unless($payment->user_id === $request->user()->id, 403);

        $paymentId = 'pay_sim_'.Str::lower(Str::random(14));
        $this->payments->markPaid($payment, $paymentId, 'upi', 'Simulated payment');

        return redirect()->to($this->returnUrl($payment))->with('success', 'Payment received (simulated).');
    }

    private function summary(Request $request, Model $payable, PaymentPurpose $purpose, float $amount): View
    {
        $address = $request->user()->address;

        return view('account.checkout.billing', [
            'payable' => $payable,
            'purpose' => $purpose,
            'amount' => $amount,
            'address' => $address,
            'taxRate' => settings()->float('payment.tax_rate_percent'),
            'countries' => config('countries'),
        ]);
    }

    private function guardSubmission(ManuscriptSubmission $submission, PaymentPurpose $purpose): ?RedirectResponse
    {
        $allowed = match ($purpose) {
            PaymentPurpose::Prescreening => $submission->stage === ManuscriptStage::Pending,
            PaymentPurpose::Publication => $submission->stage === ManuscriptStage::Approved,
            default => false,
        };

        $alreadyPaid = $submission->payments()->where('payment_purpose', $purpose)->where('payment_status', PaymentStatus::Paid)->exists();

        if (! $allowed || $alreadyPaid || ! $this->fees->paymentsEnabled()) {
            return redirect()->route('account.submissions.show', $submission)->with('error', 'This fee is not payable right now.');
        }

        return null;
    }

    private function amountFor(ManuscriptSubmission $submission, PaymentPurpose $purpose): float
    {
        return $purpose === PaymentPurpose::Publication
            ? (float) $this->fees->publicationFeeFor($submission)
            : $this->fees->prescreeningFee();
    }

    private function returnUrl(Payment $payment): string
    {
        return $payment->payable instanceof ManuscriptSubmission
            ? route('account.submissions.show', $payment->payable)
            : route('account.plagiarism-checks.show', $payment->payable_id);
    }
}
