<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 3 — author profile (clarification #4) and billing address (clarification #5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Nullable: Google/ORCID/OTP sign-ups do not collect it; it is
            // required before the first manuscript submission instead.
            $table->foreignId('author_category_id')->nullable()->constrained('manuscript_author_categories')->restrictOnDelete();
            $table->string('institution', 190)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('bio')->nullable();
            $table->string('orcid', 25)->nullable();
            $table->string('profile_picture')->nullable();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('recipient_name', 150);
            $table->string('organization_name', 190)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->char('country_code', 2);
            $table->string('state', 150)->nullable();
            $table->string('city', 150);
            $table->string('postal_code', 20)->nullable();
            $table->enum('tax_id_type', ['gst', 'vat', 'none'])->default('none');
            $table->string('tax_id_number', 40)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('author_profiles');
    }
};
