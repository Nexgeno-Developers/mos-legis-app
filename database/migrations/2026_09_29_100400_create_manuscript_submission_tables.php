<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 4 — manuscript submissions (SOW A.16) plus revision history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manuscript_submissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('stage', [
                'pending', 'plagiarism_accepted', 'rejected', 'in_review',
                'revision', 'resubmitted', 'approved', 'published',
            ])->default('pending')->index();
            $table->foreignId('assigned_to')->nullable()->index()->constrained('users')->nullOnDelete();
            // Added: needed by the reviewer-allocation tie-break ("longest since last assignment").
            $table->timestamp('assigned_at')->nullable();
            // Added: drives the "Waiting" column; updated_at is reset by any edit.
            $table->timestamp('stage_changed_at')->nullable();
            // Added: archive ordering / year filter and certificate publication date.
            $table->timestamp('published_at')->nullable()->index();
            $table->decimal('plagiarism_similarity', 5, 2)->nullable();

            // Author details
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('author_category_id')->index()->constrained('manuscript_author_categories')->restrictOnDelete();
            $table->string('institution', 190)->nullable();
            $table->string('country', 100)->nullable();
            $table->json('co_authors')->nullable();

            // Manuscript information
            $table->string('title');
            $table->foreignId('content_category_id')->index()->constrained('manuscript_content_categories')->restrictOnDelete();
            // Added: the monthly theme the manuscript was written for (SOW A.14; printed on the certificate).
            $table->foreignId('content_category_theme_id')->nullable()->constrained('manuscript_content_category_themes')->nullOnDelete();
            $table->unsignedInteger('word_count');
            $table->json('keywords')->nullable();
            $table->text('abstract');
            $table->string('manuscript_attachment');

            // Confirmations
            $table->boolean('is_original_unpublished_confirmed')->default(false);
            $table->boolean('is_coauthor_consent_confirmed')->default(false);
            $table->boolean('is_plagiarism_ai_declaration_confirmed')->default(false);
            $table->boolean('is_policies_accepted')->default(false);
            $table->boolean('is_prescreening_fee_terms_accepted')->default(false);
            $table->boolean('is_publication_fee_terms_accepted')->default(false);

            $table->index('created_at');
            // Reviewer workload lookups: active assignments per reviewer.
            $table->index(['assigned_to', 'stage']);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE manuscript_submissions ADD FULLTEXT ftx_submission_title (title)');
        }

        // Added: one row per reviewer decision, so revision requests, reviewer
        // remarks and the author's resubmitted files are never overwritten.
        Schema::create('manuscript_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_submission_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('round');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('decision', ['approved', 'revision', 'rejected']);
            $table->text('reviewer_remarks')->nullable();
            $table->string('reviewed_attachment')->comment('File that was under review when the decision was made.');
            $table->timestamp('decided_at');
            $table->string('resubmitted_attachment')->nullable();
            $table->unsignedInteger('resubmitted_word_count')->nullable();
            $table->text('author_response')->nullable();
            $table->timestamp('resubmitted_at')->nullable();
            $table->timestamps();

            $table->unique(['manuscript_submission_id', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manuscript_revisions');
        Schema::dropIfExists('manuscript_submissions');
    }
};
