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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_no', 100)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total_amount', 12, 2)->default(0);
            $table->enum('duration_type', ['monthly', 'yearly']);
            $table->enum('booking_status', ['reserved', 'active', 'completed', 'cancelled'])->default('reserved');
            $table->enum('payment_status', ['paid', 'unpaid', 'partially_paid'])->default('unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
