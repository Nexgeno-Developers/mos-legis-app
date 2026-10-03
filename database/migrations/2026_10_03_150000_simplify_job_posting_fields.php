<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Simpler job posting form: fields that are now optional or no longer asked for become nullable.
 * Existing jobs keep their values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->text('responsibilities')->nullable()->change();
            $table->text('qualifications')->nullable()->change();
            $table->text('required_skills')->nullable()->change();
            $table->string('application_email_url')->nullable()->change();
            $table->string('source_name', 150)->nullable()->change();
        });

        DB::statement("ALTER TABLE job_postings MODIFY application_method ENUM('Email','External URL') NULL");
    }

    public function down(): void
    {
        // Columns stay nullable: rows created since may have no value for them.
    }
};
