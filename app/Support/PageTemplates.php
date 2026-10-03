<?php

namespace App\Support;

use App\Enums\MetaType;
use App\Enums\PageTemplate;

/**
 * Template-specific fields stored in page_metas (SOW A.11). Repeatable
 * groups are stored as one JSON meta per key.
 */
final class PageTemplates
{
    /** Editorial board sections, in display order: key => [label in the admin, heading on the site]. */
    public const TEAM_SECTIONS = [
        'founder' => ['label' => 'Founders', 'heading' => 'Who Started the Review'],
        'board' => ['label' => 'Editorial Board', 'heading' => 'Board of Editors'],
        'advisory' => ['label' => 'Advisory Board', 'heading' => 'Counsel to the Board'],
    ];

    /**
     * @return array<string, array{label: string, type: string, columns?: array<string, array{label: string, type: string}>}>
     */
    public static function fields(PageTemplate $template): array
    {
        return match ($template) {
            PageTemplate::Teams => [
                'sections' => ['label' => 'Section headings', 'type' => 'sections'],
                'members' => ['label' => 'Team members', 'type' => 'repeater', 'columns' => [
                    'name' => ['label' => 'Name', 'type' => 'text'],
                    'designation' => ['label' => 'Designation', 'type' => 'text'],
                    'group' => ['label' => 'Section', 'type' => 'select', 'default' => 'board', 'options' => array_map(fn ($s) => $s['label'], self::TEAM_SECTIONS), 'labels_from' => 'sections'],
                    'overview' => ['label' => 'Overview', 'type' => 'textarea'],
                ]],
            ],
            PageTemplate::Patron => [
                'entries' => ['label' => 'Patron acknowledgements', 'type' => 'repeater', 'columns' => [
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'month_year' => ['label' => 'Date (Month/Year)', 'type' => 'text'],
                ]],
            ],
            PageTemplate::PaperWinner => [
                'winner_choose_desc' => ['label' => 'How the winner is chosen', 'type' => 'textarea'],
                'prize_desc' => ['label' => 'Prize description', 'type' => 'textarea'],
                'be_considered_desc' => ['label' => 'Be considered next month', 'type' => 'textarea'],
            ],
            PageTemplate::Contact => [
                'chief_editor_email' => ['label' => 'Chief editor email', 'type' => 'email'],
                'general_query_email' => ['label' => 'General query email', 'type' => 'email'],
                'telephone' => ['label' => 'Telephone', 'type' => 'text'],
                'office_address' => ['label' => 'Office address', 'type' => 'textarea'],
                'desk_hours' => ['label' => 'Desk hours', 'type' => 'text'],
                'map_embed_url' => ['label' => 'Google Maps embed URL', 'type' => 'map', 'hint' => 'Optional. In Google Maps choose Share → Embed a map and paste the link or the whole <iframe> code. Leave blank to hide the map.'],
                'faqs' => ['label' => 'FAQs', 'type' => 'repeater', 'columns' => [
                    'question' => ['label' => 'Question', 'type' => 'text'],
                    'answer' => ['label' => 'Answer', 'type' => 'textarea'],
                ]],
            ],
            PageTemplate::Career => [
                'apply_email' => ['label' => 'Careers email (shown on the page)', 'type' => 'email'],
            ],
            // Job Postings: title, intro, content and SEO only — the filters and listings are dynamic.
            PageTemplate::Jobs => [],
            // Plagiarism Checker: everything except the check form.
            PageTemplate::PlagiarismChecker => [
                'steps_label' => ['label' => '“How it works” label', 'type' => 'text'],
                'steps_heading' => ['label' => '“How it works” heading', 'type' => 'text'],
                'steps' => ['label' => 'Steps', 'type' => 'repeater', 'hint' => 'Use {fee} for the checking fee and {threshold} for the similarity limit — both are filled in from Settings.', 'columns' => [
                    'text' => ['label' => 'Step', 'type' => 'textarea'],
                ]],
                'steps_note' => ['label' => 'Note below the steps', 'type' => 'textarea'],
                'guest_heading' => ['label' => 'Signed-out heading (instead of the form)', 'type' => 'text'],
                'guest_text' => ['label' => 'Signed-out text', 'type' => 'textarea'],
            ],
            PageTemplate::Layout => [],
        };
    }

    /**
     * Team sections with the label/heading saved on the page. Saved values are used as they are —
     * an emptied field stays empty (hidden on the site). Defaults apply only to a section that was
     * never saved.
     *
     * @return array<string, array{label: string, heading: string}>
     */
    public static function teamSections(?array $saved): array
    {
        return collect(self::TEAM_SECTIONS)->map(fn ($default, $key) => [
            'label' => isset($saved[$key]) ? trim((string) ($saved[$key]['label'] ?? '')) : $default['label'],
            'heading' => isset($saved[$key]) ? trim((string) ($saved[$key]['heading'] ?? '')) : $default['heading'],
        ])->all();
    }

    /** Name of each section for the admin's Section dropdown (the default name when the label is empty). */
    public static function teamSectionNames(?array $saved): array
    {
        return collect(self::teamSections($saved))->map(fn ($section, $key) => $section['label'] ?: self::TEAM_SECTIONS[$key]['label'])->all();
    }

    /** Section key for a stored group value; older free-text values ("Founder", "Advisory panel"…) are mapped. */
    public static function teamSection(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return match (true) {
            array_key_exists($value, self::TEAM_SECTIONS) => $value,
            str_starts_with($value, 'found') => 'founder',
            str_contains($value, 'advis') || str_contains($value, 'counsel') => 'advisory',
            default => 'board',
        };
    }

    /**
     * Plagiarism Checker wording used until the CMS page exists (and seeded into it).
     *
     * @return array<string, mixed>
     */
    public static function plagiarismDefaults(): array
    {
        return [
            'steps_label' => 'How it works',
            'steps_heading' => 'What happens after you pay',
            'steps' => [
                ['text' => 'Pay the checking fee of {fee} (+ tax for Indian billing addresses).'],
                ['text' => 'Your content is sent securely to the plagiarism service.'],
                ['text' => 'See your similarity percentage and matched sources.'],
                ['text' => 'Download the report. Manuscripts above {threshold}% similarity are not accepted for review.'],
            ],
            'steps_note' => 'Standalone checks never create or change a manuscript submission.',
            'guest_heading' => 'Sign in to run a check',
            'guest_text' => 'Results and reports are saved to your author account.',
        ];
    }

    public static function metaType(array $field): MetaType
    {
        return match ($field['type']) {
            'repeater', 'sections' => MetaType::Json,
            'textarea' => MetaType::Text,
            default => MetaType::String,
        };
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            PageTemplate::Layout->value => 'Default layout',
            PageTemplate::Teams->value => 'Teams',
            PageTemplate::Patron->value => 'Patron',
            PageTemplate::PaperWinner->value => 'Paper winner',
            PageTemplate::Contact->value => 'Contact',
            PageTemplate::Career->value => 'Careers',
            PageTemplate::Jobs->value => 'Job postings',
            PageTemplate::PlagiarismChecker->value => 'Plagiarism checker',
        ];
    }
}
