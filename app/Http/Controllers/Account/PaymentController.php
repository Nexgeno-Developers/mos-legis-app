<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Documents\DocumentRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Payments & Invoices tab: payment history and invoice PDFs (certificates are on each published manuscript).
 */
class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('account.payments.index', [
            'payments' => $user->payments()->with('payable')->latest('id')->paginate(15),
        ]);
    }

    public function invoice(Request $request, Payment $payment, DocumentRenderer $documents): Response
    {
        abort_unless($payment->user_id === $request->user()->id && $payment->isPaid(), 404);

        return response($documents->invoice($payment), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$payment->invoice_number.'.pdf"',
        ]);
    }
}
