<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 4 — payments (SOW A.17, clarification #2 and #5) and plagiarism checks (SOW A.18).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->enum('payable_type', ['manuscript_submissions', 'plagiarism_checks']);
            $table->unsignedBigInteger('payable_id');
            $table->enum('payment_purpose', ['prescreening', 'publication', 'plagiarism_check']);
            // Added: unique invoice/receipt number required by clarification #2; set once paid.
            $table->string('invoice_number', 40)->nullable()->unique();
            $table->decimal('amount', 13, 3);
            $table->decimal('tax_amount', 13, 3)->default(0);
            $table->decimal('total_amount', 13, 3)->storedAs('amount + tax_amount');
            $table->char('currency', 3)->default('INR');
            $table->decimal('tax_rate', 7, 4)->nullable();
            $table->foreignId('billing_address_id')->nullable()->index()->constrained('addresses')->nullOnDelete();
            $table->char('billing_country_code', 2)->nullable();
            $table->json('billing_details')->nullable();
            // Nullable: unknown until the gateway reports how the payer paid.
            $table->string('payment_method', 40)->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->index();
            $table->string('payment_details')->nullable();
            // Added: Razorpay order id, created before checkout and verified with the signature.
            $table->string('gateway_order_id', 100)->nullable()->unique();
            $table->string('payment_id', 100)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
        });

        Schema::create('plagiarism_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('manuscript_submission_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->enum('check_type', ['manuscript', 'standalone']);
            $table->string('title');
            $table->mediumText('content')->nullable();
            $table->string('uploaded_file')->nullable();
            $table->decimal('similarity_percentage', 5, 2)->nullable();
            $table->enum('check_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('api_response')->nullable();
            $table->string('report_file')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['check_type', 'check_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plagiarism_checks');
        Schema::dropIfExists('payments');
    }
};
