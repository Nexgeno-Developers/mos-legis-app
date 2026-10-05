<?php

namespace App\Http\Controllers\Site;

use App\Enums\PageTemplate;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\PageTemplates;
use App\Support\PublicPages;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every CMS page (SOW A.11) is served here at /{slug}, using the slug and status set in
 * Admin → Pages; drafts are not found. The page's template decides what is rendered.
 * Home (/) has its own controller.
 */
class PageController extends Controller
{
    public function show(Request $request): Response
    {
        $slug = $request->path();
        $page = $slug === 'home' ? null : PublicPages::bySlug($slug);
        abort_unless($page, 404);

        $controller = match ($page->template) {
            PageTemplate::Submit => [SubmitPageController::class, '__invoke'],
            PageTemplate::PaperWinner => [BestPaperController::class, '__invoke'],
            PageTemplate::Jobs => [JobController::class, '__invoke'],
            PageTemplate::PlagiarismChecker => [PlagiarismCheckerController::class, 'show'],
            PageTemplate::Contact => [EnquiryController::class, 'contact'],
            PageTemplate::Career => [EnquiryController::class, 'careers'],
            PageTemplate::Teams => [$this, 'editorialBoard'],
            PageTemplate::Patron => [$this, 'patrons'],
            PageTemplate::Archive => [ArchiveController::class, 'index'],
            PageTemplate::Layout => null,
        };

        $view = $controller
            ? app()->call([is_string($controller[0]) ? app($controller[0]) : $controller[0], $controller[1]], ['page' => $page])
            : view('site.page', ['page' => $page]);

        return response($view);
    }

    /** Old policy addresses (/policies/{slug}) now live at /{slug}. */
    public function legacyPolicy(string $slug)
    {
        return redirect()->to(url($slug), 301);
    }

    public function editorialBoard(Page $page)
    {
        // Fixed sections (Founders, Editorial Board, Advisory Board) in display order, with the label and
        // heading set in the admin; empty sections are left out.
        $members = collect($page->meta('members', []))->groupBy(fn ($m) => PageTemplates::teamSection($m['group'] ?? null));
        $groups = collect(PageTemplates::teamSections($page->meta('sections')))
            ->map(fn ($section, $key) => $section + ['members' => $members->get($key, collect())])
            ->filter(fn ($section) => $section['members']->isNotEmpty());

        return view('site.editorial-board', compact('page', 'groups'));
    }

    public function patrons(Page $page)
    {
        return view('site.patrons', ['page' => $page, 'entries' => $page->meta('entries', [])]);
    }
}
