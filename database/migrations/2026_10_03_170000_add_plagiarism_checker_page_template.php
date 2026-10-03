<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Plagiarism Checker becomes a page managed in Admin → Pages ("Plagiarism checker" template):
 * title, intro, SEO, the "How it works" steps and the signed-out message are edited there;
 * the check form itself stays dynamic. Created with today's wording.
 */
return new class extends Migration
{
    private const TEMPLATES = "'layout','teams','patron','paper_winner','contact','career','jobs'";

    public function up(): void
    {
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.",'plagiarism_checker') NOT NULL");

        if (DB::table('pages')->where('template', 'plagiarism_checker')->exists()) {
            return;
        }

        $id = DB::table('pages')->insertGetId([
            'title' => 'Check Your Content for Similarity',
            'slug' => DB::table('pages')->where('slug', 'plagiarism-checker')->exists() ? 'plagiarism-checker-page' : 'plagiarism-checker',
            'template' => 'plagiarism_checker',
            'status' => 'Published',
            'excerpt' => 'Paste your text or upload a .docx. After payment we run it through our plagiarism service and give you a similarity score and a downloadable report.',
            'seo_title' => 'Plagiarism Checker',
            'seo_description' => 'Check your manuscript for similarity before you submit.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (self::metas() as $key => [$type, $value]) {
            DB::table('page_metas')->insert([
                'page_id' => $id, 'meta_key' => $key, 'meta_type' => $type,
                'meta_value' => $type === 'json' ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('pages')->where('template', 'plagiarism_checker')->delete();
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.') NOT NULL');
    }

    /** @return array<string, array{0: string, 1: mixed}> */
    public static function metas(): array
    {
        return [
            'steps_label' => ['string', 'How it works'],
            'steps_heading' => ['string', 'What happens after you pay'],
            'steps' => ['json', [
                ['text' => 'Pay the checking fee of {fee} (+ tax for Indian billing addresses).'],
                ['text' => 'Your content is sent securely to the plagiarism service.'],
                ['text' => 'See your similarity percentage and matched sources.'],
                ['text' => 'Download the report. Manuscripts above {threshold}% similarity are not accepted for review.'],
            ]],
            'steps_note' => ['text', 'Standalone checks never create or change a manuscript submission.'],
            'guest_heading' => ['string', 'Sign in to run a check'],
            'guest_text' => ['text', 'Results and reports are saved to your author account.'],
        ];
    }
};
