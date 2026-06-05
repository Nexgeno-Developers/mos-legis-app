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
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('cabin_id')->nullable()->constrained('cabins')->nullOnDelete();
            $table->foreignId('seat_id')->nullable()->constrained('seats')->nullOnDelete();
            $table->string('occupant_name')->nullable();
            $table->string('occupant_phone', 30)->nullable();
            $table->string('occupant_id_proof_no', 100)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('kyc_status', ['pending', 'verified'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
