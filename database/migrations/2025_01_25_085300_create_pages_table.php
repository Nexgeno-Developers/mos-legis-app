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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            //$table->string('slug')->unique(); // Unique slug for the page
            $table->string('slug'); // Unique slug for the page
            $table->string('language', 5)->default('en'); // Language code (e.g., 'en', 'fr')
            $table->string('title'); // Page title
            $table->longText('content')->nullable(); // Page content
            $table->boolean('is_active')->default(true); // Active/inactive status
            $table->string('layout')->nullable(); // Layout type for customization
            $table->string('seo_title')->nullable(); // SEO title
            $table->text('seo_description')->nullable(); // SEO description
            $table->string('seo_keywords')->nullable(); // SEO keywords
            $table->longText('seo_schema')->nullable(); // SEO schema markup
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps(); // Created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
