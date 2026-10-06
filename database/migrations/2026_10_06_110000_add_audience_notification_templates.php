<?php

use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Audience-specific manuscript and payment emails: admins, reviewers and authors each get wording
 * written for them (and the author is never told who the reviewer is). Existing templates are kept.
 */
return new class extends Migration
{
    private const SLUGS = [
        'submission_received_admin', 'submission_in_review', 'submission_assigned_reviewer', 'submission_reassigned_reviewer',
        'submission_approved_staff', 'submission_published_staff', 'payment_received_admin',           
    ];

    public function up(): void
    {
        // The seeder only creates templates that don't exist yet.
        (new NotificationTemplateSeeder)->run();
    }

    public function down(): void
    {
        DB::table('notification_templates')->whereIn('slug', self::SLUGS)->delete();
    }
};
