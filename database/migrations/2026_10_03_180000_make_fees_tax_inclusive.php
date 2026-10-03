<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fees are now inclusive of all taxes: the payer is charged exactly the fee; for Indian billing
 * addresses the tax is carved out of it (shown on the invoice). Unpaid payments created under the
 * old "fee + tax" rule are recalculated; paid payments keep what was actually charged.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')->where('payment_status', 'Pending')->orderBy('id')->each(function ($payment) {
            $fee = (float) $payment->amount; // old rule: amount was the fee, tax was added on top
            $rate = (float) $payment->tax_rate;
            $tax = $rate > 0 ? round($fee * $rate / (100 + $rate), 2) : 0.0;

            DB::table('payments')->where('id', $payment->id)->update([
                'amount' => round($fee - $tax, 2),
                'tax_amount' => $tax,
                'gateway_order_id' => null, // a new gateway order is created for the new total
            ]);
        });

        // The Plagiarism Checker step that said "+ tax".
        DB::table('page_metas')->where('meta_key', 'steps')
            ->where('meta_value', 'like', '%+ tax for Indian billing addresses%')
            ->orderBy('id')
            ->each(fn ($meta) => DB::table('page_metas')->where('id', $meta->id)->update([
                'meta_value' => str_replace('(+ tax for Indian billing addresses)', '(inclusive of all taxes)', $meta->meta_value),
            ]));
    }

    public function down(): void
    {
        // Not reversible: unpaid payments are simply recalculated again at checkout.
    }
};
