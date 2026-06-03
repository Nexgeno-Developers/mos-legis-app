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
        Schema::create('seat_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_id')->constrained('seats')->cascadeOnDelete();
            $table->enum('duration', ['daily', 'monthly', 'yearly']);
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['seat_id', 'duration']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_pricing');
    }
};
