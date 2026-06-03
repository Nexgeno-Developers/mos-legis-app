<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Page;
use App\Models\PageMeta;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the company
        $company = Company::where('email', 'info@ctrlshift.co.in')->first();

        if (!$company) {
            return;
        }

        // Get all page layouts
        $layouts = getPageLayouts();

        // Seed pages for each layout
        foreach ($layouts as $layoutSlug => $layoutData) {
            // Determine how many pages to seed based on description comment
            $pageCount = $this->getPageCountForLayout($layoutSlug);

            // Seed the specified number of pages for this layout
            for ($i = 1; $i <= $pageCount; $i++) {
                $slug = $pageCount > 1 ? "{$layoutSlug}-{$i}" : $layoutSlug;
                $title = $pageCount > 1 ? "{$layoutData['label']} {$i}" : $layoutData['label'];

                Page::firstOrCreate(
                    ['slug' => $slug, 'language' => 'en'],
                    [
                        'title' => $title,
                        'content' => '',
                        'seo_title' => '',
                        'seo_description' => '',
                        'seo_keywords' => '',
                        'layout' => $layoutSlug,
                        'is_active' => true,
                        'company_id' => $company->id,
                    ],
                );
            }
        }
    }

    /**
     * Get the number of pages to seed for a given layout.
     */
    protected function getPageCountForLayout(string $layoutSlug): int
    {
        $counts = [
            'default' => 1,
            'example' => 1,
        ];

        return $counts[$layoutSlug] ?? 1;
    }
}
