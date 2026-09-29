<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 1 — OTP verification and social accounts (SOW B.01).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 190);
            // Widened from CHAR(6): the code is stored hashed, never in plain text.
            $table->string('otp_code');
            $table->enum('purpose', ['registration', 'login', 'password_reset'])->default('registration');
            $table->unsignedTinyInteger('attempts')->default(0);
            // Explicit default: stops MySQL/MariaDB adding ON UPDATE CURRENT_TIMESTAMP to the first NOT NULL timestamp.
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['identifier', 'purpose']);
        });

        Schema::create('user_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['google', 'orcid']);
            $table->string('provider_user_id', 190);
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_social_accounts');
        Schema::dropIfExists('otp_verifications');
    }
};
