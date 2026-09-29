<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Sections 9–10 — enquiries (SOW A.19), activity logs (A.20) and settings (A.21).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->enum('form_name', ['contact', 'career'])->index();
            $table->string('name', 150);
            $table->string('email', 190);
            // Nullable: the Contact form (SOW C.06) does not ask for a phone number.
            $table->string('phone', 20)->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('form_data')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('module', 80)->index();
            $table->string('action', 190);
            $table->string('record_id', 60)->nullable();
            $table->json('payload')->nullable();
            $table->string('remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->enum('setting_group', ['general', 'manuscript', 'payment', 'seo_social']);
            $table->string('setting_key', 100);
            $table->text('setting_value')->nullable();
            $table->timestamps();

            $table->unique(['setting_group', 'setting_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('enquiries');
    }
};
