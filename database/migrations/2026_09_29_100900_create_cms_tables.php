<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 8 — CMS pages and page metas (SOW A.11).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 190);
            $table->string('slug', 220)->unique();
            $table->longText('content')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->string('featured_image')->nullable();
            $table->enum('status', ['Published', 'Draft'])->default('Draft')->index();
            $table->enum('template', ['layout', 'teams', 'patron', 'paper_winner', 'contact', 'career'])->index();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('page_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('meta_key', 120);
            $table->longText('meta_value')->nullable();
            $table->enum('meta_type', ['string', 'text', 'json', 'boolean', 'number'])->default('string');
            $table->timestamps();

            $table->unique(['page_id', 'meta_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_metas');
        Schema::dropIfExists('pages');
    }
};
