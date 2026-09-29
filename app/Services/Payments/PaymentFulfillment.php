<?php

namespace App\Services\Payments;

use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Jobs\RunPlagiarismCheck;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\PlagiarismCheck;
use App\Services\Manuscripts\ManuscriptWorkflow;

/**
 * What happens once a payment is settled.
 */
class PaymentFulfillment
{
    public function __construct(private readonly ManuscriptWorkflow $workflow) {}

    public function fulfill(Payment $payment): void
    {
        $payable = $payment->payable;

        match ($payment->payment_purpose) {
            PaymentPurpose::Prescreening => $payable instanceof ManuscriptSubmission
                ? $this->workflow->startPlagiarismCheck($payable, $payment)
                : null,
            PaymentPurpose::Publication => $payable instanceof ManuscriptSubmission && $payable->stage === ManuscriptStage::Approved
                ? $this->workflow->publish($payable)
                : null,
            PaymentPurpose::PlagiarismCheck => $payable instanceof PlagiarismCheck
                ? $this->startStandaloneCheck($payable, $payment)
                : null,
        };
    }

    private function startStandaloneCheck(PlagiarismCheck $check, Payment $payment): void
    {
        $check->forceFill(['payment_id' => $payment->id])->save();
        RunPlagiarismCheck::dispatch($check)->afterCommit();
    }
}
