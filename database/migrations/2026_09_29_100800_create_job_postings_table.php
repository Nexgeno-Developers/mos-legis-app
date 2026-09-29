<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * schema.sql Section 7 — job postings (SOW A.04, B.06, C.03). Named job_postings to avoid the queue `jobs` table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->restrictOnDelete();
            $table->string('job_title', 190);
            $table->string('organisation', 190);
            $table->string('location', 150);
            $table->enum('work_mode', ['Remote', 'Hybrid', 'On-site'])->index();
            $table->enum('employment_type', ['Full time', 'Part time', 'Internship', 'Contract', 'Fellowship'])->index();
            $table->string('experience', 60);
            $table->string('practice_area', 120)->index();
            $table->string('salary', 120)->nullable();
            $table->text('summary');
            $table->text('responsibilities');
            $table->text('qualifications');
            $table->text('required_skills');
            $table->enum('application_method', ['Email', 'External URL']);
            $table->string('application_email_url');
            $table->date('application_deadline');
            $table->date('published_date');
            $table->date('expiry_date')->index();
            $table->string('source_name', 150);
            $table->string('source_url');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();
            $table->timestamps();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE job_postings ADD FULLTEXT ftx_job_search (job_title, organisation, location, required_skills)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
