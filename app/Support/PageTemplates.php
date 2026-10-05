<?php

namespace App\Support;

use App\Enums\MetaType;
use App\Enums\PageTemplate;
use App\Models\Page;

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
                'list_label' => ['label' => 'Acknowledgements label', 'type' => 'text'],
                'list_heading' => ['label' => 'Acknowledgements heading', 'type' => 'text'],
                'entries' => ['label' => 'Patron acknowledgements', 'type' => 'repeater', 'columns' => [
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'month_year' => ['label' => 'Date (Month/Year)', 'type' => 'text'],
                ]],
            ],
            PageTemplate::PaperWinner => [
                'current_label' => ['label' => '“Current winner” label', 'type' => 'text'],
                'cards_label' => ['label' => 'Label above the three boxes', 'type' => 'text'],
                'winner_choose_title' => ['label' => 'Box 1 heading', 'type' => 'text'],
                'winner_choose_desc' => ['label' => 'Box 1 text (how the winner is chosen)', 'type' => 'textarea'],
                'prize_title' => ['label' => 'Box 2 heading', 'type' => 'text'],
                'prize_desc' => ['label' => 'Box 2 text (the prize)', 'type' => 'textarea'],
                'be_considered_title' => ['label' => 'Box 3 heading', 'type' => 'text'],
                'be_considered_desc' => ['label' => 'Box 3 text (be considered next quarter)', 'type' => 'textarea'],
                'past_label' => ['label' => 'Past winners label', 'type' => 'text'],
                'past_heading' => ['label' => 'Past winners heading', 'type' => 'text'],
            ],
            PageTemplate::Contact => [
                'form_heading' => ['label' => 'Form heading', 'type' => 'text'],
                'form_text' => ['label' => 'Form text', 'type' => 'textarea'],
                'contacts_heading' => ['label' => 'Contacts box heading', 'type' => 'text'],
                'contacts_text' => ['label' => 'Contacts box text', 'type' => 'text'],
                'chief_editor_email' => ['label' => 'Chief editor email', 'type' => 'email'],
                'general_query_email' => ['label' => 'General query email', 'type' => 'email'],
                'telephone' => ['label' => 'Telephone', 'type' => 'text'],
                'office_address' => ['label' => 'Office address', 'type' => 'textarea'],
                'desk_hours' => ['label' => 'Desk hours', 'type' => 'text'],
                'map_label' => ['label' => 'Map card label', 'type' => 'text'],
                'map_heading' => ['label' => 'Map card heading', 'type' => 'text'],
                'map_embed_url' => ['label' => 'Google Maps embed URL', 'type' => 'map', 'hint' => 'Optional. In Google Maps choose Share → Embed a map and paste the link or the whole <iframe> code. Leave blank to hide the map.'],
                'faq_label' => ['label' => 'FAQ label', 'type' => 'text'],
                'faq_heading' => ['label' => 'FAQ heading', 'type' => 'text'],
                'faq_text' => ['label' => 'FAQ text', 'type' => 'textarea'],
                'help_heading' => ['label' => '“Still have a question?” heading', 'type' => 'text'],
                'help_text' => ['label' => '“Still have a question?” text', 'type' => 'text'],
                'faqs' => ['label' => 'FAQs', 'type' => 'repeater', 'columns' => [
                    'question' => ['label' => 'Question', 'type' => 'text'],
                    'answer' => ['label' => 'Answer', 'type' => 'textarea'],
                ]],
            ],
            PageTemplate::Career => [
                'form_label' => ['label' => 'Form label', 'type' => 'text'],
                'form_heading' => ['label' => 'Form heading', 'type' => 'text'],
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
            // Submit: all wording around the dynamic parts (form, categories, fee table).
            // Placeholders {fee}, {threshold} and {currency} are filled in from Settings.
            PageTemplate::Submit => [
                'facts' => ['label' => 'Key facts (boxes above the form)', 'type' => 'repeater', 'hint' => 'Up to three work best. Use {fee}, {threshold} and {currency}.', 'columns' => [
                    'title' => ['label' => 'Bold text', 'type' => 'text'],
                    'text' => ['label' => 'Small text', 'type' => 'text'],
                ]],
                'form_label' => ['label' => 'Form label', 'type' => 'text'],
                'form_heading' => ['label' => 'Form heading', 'type' => 'text'],
                'guest_heading' => ['label' => 'Signed-out heading (instead of the form)', 'type' => 'text'],
                'guest_text' => ['label' => 'Signed-out text', 'type' => 'textarea'],
                'guide_label' => ['label' => 'Guide label', 'type' => 'text'],
                'guide_heading' => ['label' => 'Guide heading', 'type' => 'text'],
                'steps_label' => ['label' => '“How it works” label', 'type' => 'text'],
                'steps_heading' => ['label' => '“How it works” heading', 'type' => 'text'],
                'steps' => ['label' => 'Steps', 'type' => 'repeater', 'hint' => 'Use {fee}, {threshold} and {currency}.', 'columns' => [
                    'title' => ['label' => 'Step', 'type' => 'text'],
                    'text' => ['label' => 'Description', 'type' => 'textarea'],
                ]],
                'categories_label' => ['label' => 'Categories label', 'type' => 'text'],
                'categories_heading' => ['label' => 'Categories heading', 'type' => 'text'],
                'preparation_label' => ['label' => 'Preparation label', 'type' => 'text'],
                'preparation_heading' => ['label' => 'Preparation heading', 'type' => 'text'],
                'guidelines_slug' => ['label' => 'Guidelines page slug (its summary is shown)', 'type' => 'text', 'hint' => 'Leave blank to show the text below instead.'],
                'preparation_text' => ['label' => 'Preparation text (when no guidelines page is set)', 'type' => 'textarea'],
                'checklist_heading' => ['label' => 'Checklist heading', 'type' => 'text'],
                'checklist' => ['label' => 'Checklist', 'type' => 'repeater', 'columns' => [
                    'text' => ['label' => 'Item', 'type' => 'text'],
                ]],
                'fees_label' => ['label' => 'Fees label', 'type' => 'text'],
                'fees_heading' => ['label' => 'Fees heading', 'type' => 'text'],
                'fees_submission' => ['label' => '“On submission” box text', 'type' => 'text'],
                'fees_publication' => ['label' => '“After acceptance” box text', 'type' => 'text'],
                'fees_note' => ['label' => 'Note above the fee table', 'type' => 'textarea'],
                'more_label' => ['label' => '“More information” label (for the page content)', 'type' => 'text'],
                'more_heading' => ['label' => '“More information” heading', 'type' => 'text'],
                'cta_text' => ['label' => 'Closing bar text', 'type' => 'text'],
                'cta_button' => ['label' => 'Closing bar button', 'type' => 'text'],
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
     * Starting wording of the Plagiarism Checker page (seeded; see defaults()).
     *
     * @return array<string, mixed>
     */
    public static function plagiarismDefaults(): array
    {
        return [
            'steps_label' => 'How it works',
            'steps_heading' => 'What happens after you pay',
            'steps' => [
                ['text' => 'Pay the checking fee of {fee} (inclusive of all taxes).'],
                ['text' => 'Your content is sent securely to the plagiarism service.'],
                ['text' => 'See your similarity percentage and matched sources.'],
                ['text' => 'Download the report. Manuscripts above {threshold}% similarity are not accepted for review.'],
            ],
            'steps_note' => 'Standalone checks never create or change a manuscript submission.',
            'guest_heading' => 'Sign in to run a check',
            'guest_text' => 'Results and reports are saved to your author account.',
        ];
    }

    /**
     * What the page body shows that is NOT edited here: content pulled from other modules, and fixed parts
     * (header and footer are left out).
     * Shown in the page editor so admins know where each thing is managed.
     *
     * @return list<array{0: string, 1: ?string, 2: ?string}> [what is shown, admin route name, link label]
     */
    public static function dynamicNotes(Page $page): array
    {
        return match (true) {
            $page->slug === 'home' => [
                ['Hero slides (headings and buttons) — the first slide uses this page’s excerpt; the rest is fixed in the design.', null, null],
                ['Content categories strip', 'admin.content-categories.index', 'Content categories'],
                ['Latest publications (the 6 most recently published manuscripts)', 'admin.submissions.index', 'Submissions'],
                ['Best Paper of the quarter (latest quarterly award)', 'admin.best-paper-awards.index', 'Best Paper Awards'],
                ['Latest 3 blog posts', 'admin.blogs.index', 'Blogs'],
            ],
            default => match ($page->template) {
                PageTemplate::Submit => [
                    ['Manuscript submission form (3 steps, payment)', null, null],
                    ['Category cards, word limits and guidelines', 'admin.content-categories.index', 'Content categories'],
                    ['“This month’s theme” on each category', 'admin.themes.index', 'Themes'],
                    ['Fee table (author category × content category)', 'admin.fees.index', 'Fee matrix'],
                    ['Author categories in the fee table and form', 'admin.author-categories.index', 'Author categories'],
                    ['{fee}, {threshold} and {currency} — pre-screening fee, similarity limit and currency', 'admin.settings.edit', 'Settings → Manuscript / Payment'],
                    ['Guidelines summary — taken from the page whose slug is set in “Guidelines page slug”', 'admin.pages.index', 'Pages'],
                ],
                PageTemplate::PlagiarismChecker => [
                    ['The check form (paste text or upload .docx) and payment', null, null],
                    ['{fee} and {threshold} — checking fee and similarity limit', 'admin.settings.edit', 'Settings → Manuscript'],
                ],
                PageTemplate::PaperWinner => [
                    ['Current quarterly winner and past winners', 'admin.best-paper-awards.index', 'Best Paper Awards'],
                    ['Year and category filters', 'admin.content-categories.index', 'Content categories'],
                ],
                PageTemplate::Jobs => [
                    ['Job listings, search and filters (only live, approved jobs)', 'admin.job-postings.index', 'Job postings'],
                ],
                PageTemplate::Contact => [
                    ['Contact form (name, email, phone, purpose, message) — the purposes list is fixed; messages arrive in Enquiries', 'admin.enquiries.index', 'Enquiries'],
                ],
                PageTemplate::Career => [
                    ['Application form with résumé upload — applications arrive in Enquiries', 'admin.enquiries.index', 'Enquiries'],
                ],
                default => [],
            },
        };

        return $notes;
    }

    /**
     * Starting wording for a page of this template: seeded with the page and prefilled when an admin creates one.
     * The website never falls back to it — it shows only what is saved.
     *
     * @return array<string, mixed>
     */
    public static function defaults(PageTemplate $template): array
    {
        return match ($template) {
            PageTemplate::Submit => self::submitDefaults(),
            PageTemplate::PlagiarismChecker => self::plagiarismDefaults(),
            PageTemplate::PaperWinner => [
                'current_label' => 'Current Winner', 'cards_label' => 'The Award',
                'winner_choose_title' => 'How Winners Are Chosen', 'prize_title' => 'The Prize',
                'be_considered_title' => 'Be Considered Next Quarter',
                'past_label' => 'Archive', 'past_heading' => 'Past Winners',
            ],
            PageTemplate::Contact => [
                'form_heading' => 'Send us a message',
                'form_text' => 'Fill in the form and the right desk will reply by email, usually within two working days.',
                'contacts_heading' => 'Editorial contacts', 'contacts_text' => 'Prefer email? Write to us directly.',
                'map_label' => 'Visit us', 'map_heading' => 'MOS Legis Editorial Office',
                'faq_label' => 'Before you write', 'faq_heading' => 'Common questions',
                'faq_text' => 'Quick answers about submissions, review timelines and fees.',
                'help_heading' => 'Still have a question?', 'help_text' => 'Our editorial desk is happy to help.',
            ],
            PageTemplate::Career => ['form_label' => 'Apply', 'form_heading' => 'Application Form'],
            PageTemplate::Patron => ['list_label' => 'With Gratitude', 'list_heading' => 'Acknowledgements'],
            default => [],
        };
    }

    /** Stores the starting wording for any field the page doesn't have yet (never overwrites). */
    public static function storeMissingDefaults(Page $page): void
    {
        $fields = self::fields($page->template);
        $existing = $page->metas()->pluck('meta_key')->all();

        foreach (self::defaults($page->template) as $key => $value) {
            if (! isset($fields[$key]) || in_array($key, $existing, true)) {
                continue;
            }
            $type = self::metaType($fields[$key]);
            $page->metas()->create([
                'meta_key' => $key,
                'meta_type' => $type,
                'meta_value' => $type === MetaType::Json ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
            ]);
        }
    }

    /**
     * Starting wording of the Submit page (seeded; see defaults()).
     *
     * @return array<string, mixed>
     */
    public static function submitDefaults(): array
    {
        return [
            'facts' => [
                ['title' => '{fee} pre-screening fee', 'text' => 'Inclusive of all taxes'],
                ['title' => '.docx within the word limit', 'text' => 'See the word limit of your category below'],
                ['title' => 'Double-blind peer review', 'text' => 'After similarity screening (max {threshold}%)'],
            ],
            'form_label' => 'Submit in three steps',
            'form_heading' => 'Manuscript Submission Form',
            'guest_heading' => 'Sign in to submit',
            'guest_text' => 'Create a free author account to submit your manuscript, pay the pre-screening fee and track every stage of review.',
            'guide_label' => 'Submission guide',
            'guide_heading' => 'Everything you need before you submit',
            'steps_label' => 'How it works',
            'steps_heading' => 'From Submission to Publication',
            'steps' => [
                ['title' => 'Submit & pay', 'text' => 'Fill in the form and pay the pre-screening fee of {fee}.'],
                ['title' => 'Plagiarism screening', 'text' => 'Manuscripts above {threshold}% similarity are declined.'],
                ['title' => 'Peer review', 'text' => 'A subject reviewer is assigned automatically.'],
                ['title' => 'Revision or approval', 'text' => 'Revise and resubmit if the reviewer asks for changes.'],
                ['title' => 'Publication', 'text' => 'Pay the publication fee and receive your certificate.'],
            ],
            'categories_label' => 'Choose a category',
            'categories_heading' => 'Categories & Word Limits',
            'preparation_label' => 'Prepare',
            'preparation_heading' => 'Preparing Your Manuscript',
            'guidelines_slug' => 'author-guidelines',
            'preparation_text' => 'Follow the formatting and citation rules of your category, and keep the manuscript anonymous for double-blind review.',
            'checklist_heading' => 'Submission Checklist',
            'checklist' => array_map(fn ($text) => ['text' => $text], [
                'Manuscript in .docx format within the category word limit', 'Abstract of not more than 250 words', 'Three to six keywords',
                'Co-authors listed and consenting', 'Originality, plagiarism and AI-use declarations ready', 'Billing address for the pre-screening invoice',
            ]),
            'fees_label' => 'Fees',
            'fees_heading' => 'Fee Structure',
            'fees_submission' => 'Plagiarism pre-screening fee',
            'fees_publication' => 'Depends on your author category and content category (table below)',
            'fees_note' => 'All fees are in {currency} and inclusive of all taxes.',
            'more_label' => 'Good to know',
            'more_heading' => 'More Information',
            'cta_text' => 'Ready to submit your manuscript?',
            'cta_button' => 'Back to the form',
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
            PageTemplate::Submit->value => 'Submit manuscript',
        ];
    }
}
