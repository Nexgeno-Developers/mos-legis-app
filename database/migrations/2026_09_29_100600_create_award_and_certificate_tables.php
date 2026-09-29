<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 5 — Best Paper awards (clarification #3) and publication certificates (clarification #1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('best_paper_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_submission_id')->index()->constrained()->cascadeOnDelete();
            $table->enum('period_type', ['monthly', 'quarterly']);
            $table->string('award_month', 15)->nullable();
            $table->string('award_quarter', 2)->nullable();
            $table->year('award_year')->index();
            $table->decimal('prize_amount', 10, 2)->default(2000);
            $table->text('editorial_citation');
            $table->date('selected_at');
            $table->foreignId('selected_by')->constrained('users')->restrictOnDelete();
            // One non-NULL key per period so the unique index really enforces "one winner per period".
            $table->string('period_key', 30)
                ->storedAs("CONCAT(period_type, ':', award_year, ':', COALESCE(award_month, award_quarter, ''))")
                ->unique();
            $table->timestamps();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE best_paper_awards ADD CONSTRAINT chk_award_period_fields CHECK (
                (period_type = 'monthly' AND award_month IS NOT NULL AND award_quarter IS NULL)
                OR (period_type = 'quarterly' AND award_quarter IS NOT NULL AND award_month IS NULL))");
        }

        Schema::create('publication_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('certificate_number', 40)->unique();
            $table->string('document_path');
            $table->string('verification_slug', 60)->unique();
            $table->json('snapshot_json')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_certificates');
        Schema::dropIfExists('best_paper_awards');
    }
};
