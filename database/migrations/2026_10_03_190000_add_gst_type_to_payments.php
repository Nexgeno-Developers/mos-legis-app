<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GST type of each taxed payment, fixed when the payment is created:
 * "intra" = buyer in the business state (CGST + SGST, half each), "inter" = other Indian state (IGST).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gst_type', 10)->nullable()->after('tax_rate');
        });

        // Existing taxed payments: decide from the billing state on the payment (business state: Maharashtra).
        DB::table('payments')->where('tax_amount', '>', 0)->orderBy('id')->each(function ($payment) {
            $state = strtolower(trim((string) (json_decode((string) $payment->billing_details, true)['state'] ?? '')));
            DB::table('payments')->where('id', $payment->id)->update(['gst_type' => $state === 'maharashtra' ? 'intra' : 'inter']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('gst_type');
        });
    }
};
