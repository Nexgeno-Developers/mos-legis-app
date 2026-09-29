<?php

namespace Database\Seeders;

use App\Enums\PageTemplate;
use App\Enums\PublishStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * SOW A.11 CMS pages with the copy from the approved wireframes
 * (database/seeders/data/pages.json). Existing pages are never overwritten.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = json_decode(file_get_contents(database_path('seeders/data/pages.json')), true, flags: JSON_THROW_ON_ERROR);
        $pages[] = $this->aboutPage();

        foreach ($pages as $data) {
            $page = Page::firstOrCreate(['slug' => $data['slug']], [
                'title' => $data['title'],
                'template' => PageTemplate::from($data['template']),
                'status' => PublishStatus::from($data['status']),
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'seo_title' => $data['seo_title'],
                'seo_description' => $data['seo_description'],
            ]);

            if (! $page->wasRecentlyCreated) {
                continue;
            }

            foreach ($data['metas'] as $key => $meta) {
                $page->metas()->create([
                    'meta_key' => $key,
                    'meta_type' => $meta['type'],
                    'meta_value' => $meta['type'] === 'json' ? json_encode($meta['value'], JSON_UNESCAPED_UNICODE) : $meta['value'],
                ]);
            }
        }
    }

    /** "About" is built as a standard layout page (wireframe screen, managed through Pages). */
    private function aboutPage(): array
    {
        $sections = [
            'Aim' => 'MOS Legis is a peer-reviewed law journal committed to publishing scholarship that is methodologically sound, doctrinally careful and useful to the people who practise, teach and reform the law.',
            'Scope' => 'We publish rigorous scholarship across constitutional, criminal, corporate, technology, environmental and comparative law, alongside case and legislative commentaries.',
            'Double-blind review' => 'Every manuscript is stripped of identifying data and assessed by reviewers on originality, methodology, argumentation and citation discipline.',
            'Editorial independence' => 'Editorial decisions rest solely with the Board. No submission is advanced, delayed or declined on grounds of affiliation, institution or patronage.',
            'Open access' => 'Published work is freely readable, permanently archived by volume and theme, and citable with a stable reference.',
        ];

        return [
            'title' => 'About MOS Legis',
            'slug' => 'about',
            'status' => 'Published',
            'template' => 'layout',
            'excerpt' => 'The aim, scope, editorial philosophy and double-blind review process of MOS Legis.',
            'content' => collect($sections)->map(fn ($text, $heading) => "<h2>{$heading}</h2>\n<p>{$text}</p>")->implode("\n"),
            'seo_title' => 'About | MOS Legis',
            'seo_description' => 'The aim, scope, editorial philosophy and double-blind review process of MOS Legis.',
            'metas' => [],
        ];
    }
}
