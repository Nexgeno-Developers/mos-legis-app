<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('payable_type', 100);
            $table->unsignedBigInteger('payable_id');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['online', 'cash', 'cheque', 'bank_transfer'])->default('online');
            $table->enum('payment_status', ['pending', 'processing', 'paid', 'failed', 'refunded'])->default('pending');
            $table->json('payment_details')->nullable();
            $table->string('payment_id', 100)->nullable()->unique();
            $table->text('remarks')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id'], 'idx_payable');
            $table->index('payment_status', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
