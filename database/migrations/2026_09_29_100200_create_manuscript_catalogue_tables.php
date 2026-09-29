<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 2 — author/content categories, themes, fee matrix and
 * reviewer ↔ content-category assignment (SOW A.09, A.12–A.15).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manuscript_author_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('manuscript_content_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->unsignedInteger('min_word_limit');
            $table->unsignedInteger('max_word_limit');
            $table->text('guideline');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE manuscript_content_categories ADD CONSTRAINT chk_content_category_word_limits CHECK (max_word_limit >= min_word_limit)');
        }

        Schema::create('manuscript_content_category_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_category_id')->constrained('manuscript_content_categories')->cascadeOnDelete();
            $table->string('name', 190);
            $table->unsignedInteger('volume');
            // Stored as the first day of the theme month so it can be sorted and
            // filtered by year; displayed as "September 2026" (SOW A.14 "Date (Month & Year)").
            $table->date('period')->index();
            $table->timestamps();
        });

        Schema::create('manuscript_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_category_id')->constrained('manuscript_content_categories')->cascadeOnDelete();
            $table->foreignId('author_category_id')->constrained('manuscript_author_categories')->cascadeOnDelete();
            $table->decimal('fees', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['author_category_id', 'content_category_id']);
        });

        Schema::create('reviewer_content_categories', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_category_id')->constrained('manuscript_content_categories')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->primary(['user_id', 'content_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviewer_content_categories');
        Schema::dropIfExists('manuscript_fees');
        Schema::dropIfExists('manuscript_content_category_themes');
        Schema::dropIfExists('manuscript_content_categories');
        Schema::dropIfExists('manuscript_author_categories');
    }
};
