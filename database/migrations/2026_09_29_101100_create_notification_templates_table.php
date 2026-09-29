<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('sms_template')->nullable();
            $table->json('sms_options')->nullable();
            $table->longText('whatsapp_template')->nullable();
            $table->json('whatsapp_options')->nullable();
            $table->string('email_subject')->nullable();
            $table->longText('email_template')->nullable();
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('whatsapp_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
