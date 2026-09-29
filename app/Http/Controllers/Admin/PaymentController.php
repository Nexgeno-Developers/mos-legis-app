<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Documents\DocumentRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

/**
 * SOW A.17 — Manuscript Payments Management (read-only ledger with invoices).
 */
class PaymentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:payments.view')];
    }

    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with(['user:id,name,email', 'payable'])
            ->when($request->string('search')->trim()->value(), function ($q, $search) {
                $id = (int) preg_replace('/\D/', '', $search);
                $q->where(fn ($q) => $q->where('payment_id', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->when($id, fn ($q) => $q->orWhere('id', $id)));
            })
            ->when($request->string('submission')->trim()->value(), fn ($q, $ref) => $q
                ->where('payable_type', 'manuscript_submissions')
                ->where('payable_id', (int) preg_replace('/\D/', '', $ref)))
            ->when($request->string('user')->trim()->value(), fn ($q, $user) => $q->whereHas('user', fn ($q) => $q
                ->where('name', 'like', "%{$user}%")->orWhere('email', 'like', "%{$user}%")))
            ->when($request->enum('status', PaymentStatus::class), fn ($q, $status) => $q->where('payment_status', $status))
            ->when($request->enum('purpose', PaymentPurpose::class), fn ($q, $purpose) => $q->where('payment_purpose', $purpose))
            ->when($request->string('method')->value(), fn ($q, $method) => $q->where('payment_method', $method))
            ->when($request->date('from'), fn ($q, $from) => $q->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($q, $to) => $q->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'methods' => Payment::query()->whereNotNull('payment_method')->distinct()->orderBy('payment_method')->pluck('payment_method', 'payment_method'),
            'totalPaid' => Payment::where('payment_status', PaymentStatus::Paid)->sum('total_amount'),
        ]);
    }

    public function show(Payment $payment): View
    {
        return view('admin.payments.show', ['payment' => $payment->load(['user', 'payable'])]);
    }

    public function invoice(Payment $payment, DocumentRenderer $documents): Response
    {
        return response($documents->invoice($payment), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.($payment->invoice_number ?? 'payment-'.$payment->id).'.pdf"',
        ]);
    }
}
