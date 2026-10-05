<?php

namespace Database\Seeders;

use App\Enums\MenuLinkType;
use App\Enums\MenuLocation;
use App\Enums\PageTemplate;
use App\Enums\PublishStatus;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Default header and footer menus (the navigation the site shipped with).
 * A menu that already has items is left untouched, so admin changes are never overwritten.
 */
class MenuSeeder extends Seeder
{
    /** Menu targets below that are CMS pages => their template. */
    private const PAGE_TEMPLATES = [
        'about' => 'layout', 'editorial-board' => 'teams', 'patrons' => 'patron', 'submit' => 'submit',
        'best-paper' => 'paper_winner', 'plagiarism-checker' => 'plagiarism_checker', 'jobs.index' => 'jobs',
        'careers' => 'career', 'contact' => 'contact',
    ];

    public function run(): void
    {
        $this->seed(MenuLocation::Header, [
            ['Home', 'home'],
            ['Journal', [['About the Journal', 'about'], ['Editorial Board', 'editorial-board'], ['Patrons', 'patrons']]],
            ['Submit', 'submit'],
            ['Archive', 'archive.index'],
            ['Best Paper', 'best-paper'],
            ['Plagiarism Checker', 'plagiarism-checker'],
            ['Blogs', 'blogs.index'],
            ['Jobs', [['Job Board', 'jobs.index'], ['Careers at MOS Legis', 'careers']]],
            ['Contact', 'contact'],
        ]);

        $policies = Page::where('template', PageTemplate::Layout)->where('status', PublishStatus::Published)
            ->whereNotIn('slug', ['home', 'about'])->orderBy('title')->get(['id', 'title']);

        $this->seed(MenuLocation::Footer, [
            ['Quick Links', [
                ['Home', 'home'], ['About', 'about'], ['Submit', 'submit'], ['Archive', 'archive.index'],
                ['Editorial Board', 'editorial-board'], ['Careers', 'careers'], ['Contact', 'contact'],
            ]],
            ['Policy & Compliance', $policies->map(fn (Page $page) => [$page->title, $page])->all()],
        ]);
    }

    /** @param  list<array{0: string, 1: string|Page|list<array{0: string, 1: string|Page}>}>  $items */
    private function seed(MenuLocation $location, array $items): void
    {
        $menu = Menu::forLocation($location);

        if ($menu->items()->exists()) {
            return;
        }

        foreach ($items as $position => [$label, $target]) {
            if (is_array($target)) {
                $group = $menu->items()->create(['label' => $label, 'link_type' => MenuLinkType::None, 'sort_order' => $position]);

                foreach ($target as $childPosition => [$childLabel, $childTarget]) {
                    $menu->items()->create($this->link($childLabel, $childTarget) + ['parent_id' => $group->id, 'sort_order' => $childPosition]);
                }

                continue;
            }

            $menu->items()->create($this->link($label, $target) + ['sort_order' => $position]);
        }
    }

    private function link(string $label, string|Page $target): array
    {
        // CMS pages are linked as pages, so the menu follows their slug and status.
        if (is_string($target) && isset(self::PAGE_TEMPLATES[$target])) {
            $target = ($target === 'about' ? Page::where('slug', 'about') : Page::where('template', self::PAGE_TEMPLATES[$target]))->oldest('id')->first() ?? $target;
        }

        return $target instanceof Page
            ? ['label' => $label, 'link_type' => MenuLinkType::Page, 'page_id' => $target->id]
            : ['label' => $label, 'link_type' => MenuLinkType::Route, 'route_name' => $target];
    }
}
