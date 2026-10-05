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
    /** Templates of the one-off system pages (created by migrations, never from Admin → Pages). */
    public const SYSTEM = [PageTemplate::Home, PageTemplate::Archive];

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
            // Best Paper: title, intro, content and SEO only — winners, headings and filters are fixed.
            PageTemplate::PaperWinner => [],
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
            // Home page: all wording; the counts, categories, articles and blog posts are dynamic.
            // {fee} and {threshold} are filled in from Settings.
            PageTemplate::Home => [
                'hero_label' => ['label' => 'Hero — label above the heading', 'type' => 'text'],
                'hero_heading' => ['label' => 'Hero — heading (each line on its own row)', 'type' => 'textarea'],
                'hero_primary' => ['label' => 'Hero — main button (Submit page)', 'type' => 'text'],
                'hero_secondary' => ['label' => 'Hero — second button (Archive)', 'type' => 'text'],
                'stat_articles' => ['label' => 'At a glance — “published articles” label', 'type' => 'text'],
                'stat_authors' => ['label' => 'At a glance — “published authors” label', 'type' => 'text'],
                'stat_categories' => ['label' => 'At a glance — “content categories” label', 'type' => 'text'],
                'stat_awards' => ['label' => 'At a glance — “Best Paper awards” label', 'type' => 'text'],
                'features_label' => ['label' => 'Why publish — label', 'type' => 'text'],
                'features_heading' => ['label' => 'Why publish — heading', 'type' => 'text'],
                'features' => ['label' => 'Why publish — points', 'type' => 'repeater', 'hint' => 'Use {fee} and {threshold}. Icons follow the order of the points.', 'columns' => [
                    'title' => ['label' => 'Title', 'type' => 'text'],
                    'text' => ['label' => 'Text', 'type' => 'textarea'],
                ]],
                'steps_label' => ['label' => 'How it works — label', 'type' => 'text'],
                'steps_heading' => ['label' => 'How it works — heading', 'type' => 'text'],
                'steps_link' => ['label' => 'How it works — link to the submission guide', 'type' => 'text'],
                'steps' => ['label' => 'How it works — steps', 'type' => 'repeater', 'hint' => 'Use {fee} and {threshold}.', 'columns' => [
                    'title' => ['label' => 'Step', 'type' => 'text'],
                    'text' => ['label' => 'Description', 'type' => 'textarea'],
                ]],
                'categories_label' => ['label' => 'Categories — label', 'type' => 'text'],
                'categories_heading' => ['label' => 'Categories — heading', 'type' => 'text'],
                'latest_label' => ['label' => 'Latest publications — label', 'type' => 'text'],
                'latest_heading' => ['label' => 'Latest publications — heading', 'type' => 'text'],
                'latest_link' => ['label' => 'Latest publications — link to the archive', 'type' => 'text'],
                'blog_label' => ['label' => 'Blog — label', 'type' => 'text'],
                'blog_heading' => ['label' => 'Blog — heading', 'type' => 'text'],
                'blog_link' => ['label' => 'Blog — link to all posts', 'type' => 'text'],
                'cta_label' => ['label' => 'Closing band — label', 'type' => 'text'],
                'cta_heading' => ['label' => 'Closing band — heading', 'type' => 'text'],
                'cta_text' => ['label' => 'Closing band — text', 'type' => 'textarea'],
                'cta_primary' => ['label' => 'Closing band — main button (Submit page)', 'type' => 'text'],
                'cta_secondary' => ['label' => 'Closing band — second button (Plagiarism Checker)', 'type' => 'text'],
            ],
            // Journal Archive: title, intro, content and SEO only — search, filters and articles are dynamic.
            PageTemplate::Archive => [],
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
                ['The line under the hero heading is this page’s excerpt', null, null],
                ['Journal at a glance — the numbers are counted automatically', null, null],
                ['{fee} and {threshold} in the text — pre-screening fee and similarity limit', 'admin.settings.edit', 'Settings'],
                ['Browse by category (with article counts)', 'admin.content-categories.index', 'Content categories'],
                ['Latest publications (the 3 most recently published manuscripts)', 'admin.submissions.index', 'Submissions'],
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
                PageTemplate::Archive => [
                    ['Published manuscripts, search, sort and the “Download all (ZIP)” button', 'admin.submissions.index', 'Submissions'],
                    ['Category filter with article counts', 'admin.content-categories.index', 'Content categories'],
                    ['Best Paper badges on winning articles', 'admin.best-paper-awards.index', 'Best Paper Awards'],
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
            PageTemplate::Contact => [
                'form_heading' => 'Send us a message',
                'form_text' => 'Fill in the form and the right desk will reply by email, usually within two working days.',
                'contacts_heading' => 'Editorial contacts', 'contacts_text' => 'Prefer email? Write to us directly.',
                'map_label' => 'Visit us', 'map_heading' => 'MOS Legis Editorial Office',
                'faq_label' => 'Before you write', 'faq_heading' => 'Common questions',
                'faq_text' => 'Quick answers about submissions, review timelines and fees.',
                'help_heading' => 'Still have a question?', 'help_text' => 'Our editorial desk is happy to help.',
            ],
            PageTemplate::Home => [
                'hero_label' => 'Peer-Reviewed Legal Scholarship',
                'hero_heading' => "Rooted in Tradition.\nDriven by Justice.",
                'hero_primary' => 'Submit a Manuscript',
                'hero_secondary' => 'Browse the Archive',
                'stat_articles' => 'Published articles', 'stat_authors' => 'Published authors',
                'stat_categories' => 'Content categories', 'stat_awards' => 'Best Paper awards',
                'features_label' => 'Why publish with us',
                'features_heading' => 'A rigorous, transparent path to publication',
                'features' => [
                    ['title' => 'Double-blind peer review', 'text' => 'Every manuscript is reviewed by a subject expert. Authors and reviewers never see each other’s names.'],
                    ['title' => 'Plagiarism screening', 'text' => 'Each submission is checked for similarity first; manuscripts above {threshold}% are not sent for review.'],
                    ['title' => 'Pay only after acceptance', 'text' => 'Only the {fee} pre-screening fee is due on submission. The publication fee follows acceptance.'],
                    ['title' => 'Quarterly Best Paper', 'text' => 'Each quarter the editorial board recognises one published paper for depth, originality and clarity.'],
                    ['title' => 'Certificate of publication', 'text' => 'Every published author receives a verifiable certificate of publication.'],
                    ['title' => 'Open archive', 'text' => 'Published articles are free to read and download from the Journal Archive.'],
                ],
                'steps_label' => 'How it works',
                'steps_heading' => 'From submission to publication',
                'steps_link' => 'Full submission guide',
                'steps' => [
                    ['title' => 'Submit', 'text' => 'Upload your .docx manuscript and pay the pre-screening fee.'],
                    ['title' => 'Screening', 'text' => 'Similarity check — up to {threshold}% is accepted for review.'],
                    ['title' => 'Peer review', 'text' => 'A subject reviewer is assigned automatically.'],
                    ['title' => 'Decision', 'text' => 'Revise and resubmit if asked, or receive approval.'],
                    ['title' => 'Publication', 'text' => 'Pay the publication fee and receive your certificate.'],
                ],
                'categories_label' => 'Explore', 'categories_heading' => 'Browse by category',
                'latest_label' => 'Latest Publications', 'latest_heading' => 'Recently published scholarship',
                'latest_link' => 'View full archive',
                'blog_label' => 'From the Blog', 'blog_heading' => 'Legal commentary', 'blog_link' => 'All posts',
                'cta_label' => 'Ready when you are',
                'cta_heading' => 'Submit your manuscript today',
                'cta_text' => 'Only the {fee} pre-screening fee is due upfront — the publication fee is payable after acceptance. Want to check your draft first? Run it through our plagiarism checker.',
                'cta_primary' => 'Start a Submission',
                'cta_secondary' => 'Plagiarism Checker',
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
            PageTemplate::Archive->value => 'Journal archive',
            PageTemplate::Home->value => 'Home',
        ];
    }
}
