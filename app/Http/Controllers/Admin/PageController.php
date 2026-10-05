<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Pages\SavePage;
use App\Enums\PageTemplate;
use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Support\PageTemplates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * SOW A.11 — Pages Management.
 */
class PageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:pages.view', only: ['index']),
            new Middleware('can:pages.create', only: ['create', 'duplicate']),
            new Middleware('can:pages.edit', only: ['edit', 'toggleStatus']),
            new Middleware('can:pages.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $pages = Page::query()
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->when($request->enum('template', PageTemplate::class), fn ($q, $template) => $q->where('template', $template))
            ->when($request->enum('status', PublishStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.index', ['pages' => $pages, 'templates' => PageTemplates::labels()]);
    }

    public function create(Request $request): View
    {
        $template = $request->enum('template', PageTemplate::class) ?? PageTemplate::Layout;

        return view('admin.pages.form', [
            'page' => new Page(['template' => $template, 'status' => PublishStatus::Draft]),
            'fields' => PageTemplates::fields($template),
            'templates' => PageTemplates::labels(),
        ]);
    }

    public function store(PageRequest $request, SavePage $savePage): RedirectResponse
    {
        $page = $savePage->handle($request->validated(), editorId: $request->user()->id);
        activity()->log('Pages', 'Created page', $page, $request->safe()->except(['content', 'featured_image', 'og_image']));

        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page created.');
    }

    public function edit(Page $page): View
    {
        $page->load('metas');

        return view('admin.pages.form', [
            'page' => $page,
            'fields' => PageTemplates::fields($page->template),
            'templates' => PageTemplates::labels(),
        ]);
    }

    public function update(PageRequest $request, Page $page, SavePage $savePage): RedirectResponse
    {
        $savePage->handle($request->validated(), $page, $request->user()->id);
        activity()->log('Pages', 'Updated page', $page, $request->safe()->except(['content', 'featured_image', 'og_image']));

        return back()->with('success', 'Page saved.');
    }

    public function toggleStatus(Page $page): RedirectResponse
    {
        if ($page->isHome()) {
            return back()->with('error', 'The home page is always published.');
        }

        $page->update([
            'status' => $page->status === PublishStatus::Published ? PublishStatus::Draft : PublishStatus::Published,
            'updated_by' => auth()->id(),
        ]);
        activity()->log('Pages', 'Changed status to '.$page->status->value, $page);

        return back()->with('success', "{$page->title} is now {$page->status->value}.");
    }

    public function duplicate(Page $page): RedirectResponse
    {
        $copy = DB::transaction(function () use ($page) {
            $copy = $page->replicate(['slug', 'status']);
            $copy->title = $page->title.' (Copy)';
            $copy->slug = $this->uniqueSlug($page->slug.'-copy');
            $copy->status = PublishStatus::Draft;
            $copy->updated_by = auth()->id();
            $copy->save();

            foreach ($page->metas as $meta) {
                $copy->metas()->create($meta->only(['meta_key', 'meta_value', 'meta_type']));
            }

            return $copy;
        });

        activity()->log('Pages', 'Duplicated page', $copy, ['source' => $page->id]);

        return redirect()->route('admin.pages.edit', $copy)->with('success', 'Page duplicated as a draft.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->isHome()) {
            return back()->with('error', 'The home page cannot be deleted.');
        }

        activity()->log('Pages', 'Deleted page', $page, ['title' => $page->title, 'slug' => $page->slug]);

        Storage::disk('public')->delete(array_filter([$page->featured_image, $page->og_image]));
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    private function uniqueSlug(string $slug): string
    {
        $candidate = $slug;
        $i = 2;

        while (Page::where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-{$i}";
            $i++;
        }

        return $candidate;
    }
}
