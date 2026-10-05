<?php

use App\Support\Settings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Settings → Approvals: author blog posts, blog comments and author job postings can each
 * require admin approval.
 * - New "approvals" settings group; the existing blog-post setting moves there from General (its value is kept).
 * - Job postings get approved_at: a job is listed only once approved; existing jobs count as approved.
 * - Notification templates for job approval and for telling authors their post/job was approved.
 */
return new class extends Migration
{
    private const TEMPLATES = [
        'job_pending_approval' => ['Job awaiting approval', 'Job posting awaiting approval',
            'The job posting "{title}" by {author_name} is awaiting approval.'],
        'job_approved' => ['Job approved', 'Your job posting is live',
            'Your job posting "{title}" has been approved and is now listed on the job board.'],
        'blog_approved' => ['Blog approved', 'Your blog post is live',
            'Your blog post "{title}" has been approved and is now published.'],
    ];

    public function up(): void
    {
        DB::statement("ALTER TABLE settings MODIFY setting_group ENUM('general','manuscript','payment','seo_social','approvals') NOT NULL");
        DB::table('settings')->where('setting_group', 'general')->where('setting_key', 'blog_author_approval_required')
            ->update(['setting_group' => 'approvals']);

        if (! Schema::hasColumn('job_postings', 'approved_at')) {
            Schema::table('job_postings', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable()->after('status');
            });
            DB::table('job_postings')->update(['approved_at' => DB::raw('created_at')]);
        }

        foreach (self::TEMPLATES as $slug => [$name, $subject, $body]) {
            if (DB::table('notification_templates')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('notification_templates')->insert([
                'slug' => $slug,
                'name' => $name,
                'email_subject' => $subject,
                'email_template' => '<p>Dear {recipient_name},</p><p>'.$body.'</p><p>— MOS Legis Editorial Office</p>',
                'sms_template' => 'MOS Legis: '.$body,
                'whatsapp_template' => $slug,
                'email_enabled' => true,
                'sms_enabled' => true,
                'whatsapp_enabled' => true,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        app(Settings::class)->flush();
    }

    public function down(): void
    {
        DB::table('notification_templates')->whereIn('slug', array_keys(self::TEMPLATES))->delete();

        if (Schema::hasColumn('job_postings', 'approved_at')) {
            Schema::table('job_postings', fn (Blueprint $table) => $table->dropColumn('approved_at'));
        }

        DB::table('settings')->where('setting_group', 'approvals')->where('setting_key', 'blog_author_approval_required')
            ->update(['setting_group' => 'general']);
        DB::table('settings')->where('setting_group', 'approvals')->delete();
        DB::statement("ALTER TABLE settings MODIFY setting_group ENUM('general','manuscript','payment','seo_social') NOT NULL");
    }
};
