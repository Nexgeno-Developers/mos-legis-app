<?php

namespace App\Http\Controllers\Site;

use App\Enums\PageTemplate;
use App\Http\Controllers\Controller;
use App\Support\PageTemplates;
use App\Support\PublicPages;
use Illuminate\View\View;

/**
 * CMS-driven pages (SOW A.11): policies and other default-layout pages, About,
 * Editorial Board (teams) and Patron Acknowledgements (C.05).
 */
class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = PublicPages::bySlug($slug);
        abort_unless($page && $page->template === PageTemplate::Layout, 404);

        return view('site.page', ['page' => $page]);
    }

    public function about(): View
    {
        return $this->show('about');
    }

    public function editorialBoard(): View
    {
        $page = PublicPages::byTemplate(PageTemplate::Teams, 'editorial-board');
        abort_unless($page, 404);

        // Fixed sections (Founders, Editorial Board, Advisory Board) in display order, with the label and
        // heading set in the admin; empty sections are left out.
        $members = collect($page->meta('members', []))->groupBy(fn ($m) => PageTemplates::teamSection($m['group'] ?? null));
        $groups = collect(PageTemplates::teamSections($page->meta('sections')))
            ->map(fn ($section, $key) => $section + ['members' => $members->get($key, collect())])
            ->filter(fn ($section) => $section['members']->isNotEmpty());

        return view('site.editorial-board', compact('page', 'groups'));
    }

    public function patrons(): View
    {
        $page = PublicPages::byTemplate(PageTemplate::Patron, 'patrons');
        abort_unless($page, 404);

        return view('site.patrons', ['page' => $page, 'entries' => $page->meta('entries', [])]);
    }
}
