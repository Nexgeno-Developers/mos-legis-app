<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentCategoryThemeRequest;
use App\Models\ContentCategory;
use App\Models\ContentCategoryTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * SOW A.14 — optional monthly theme per content category ("Vol. 5 · AI in Law · September 2026").
 */
class ContentCategoryThemeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:themes.view', only: ['index']),
            new Middleware('can:themes.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $themes = ContentCategoryTheme::query()
            ->with('contentCategory:id,name')
            ->when($search, function ($q) use ($search) {
                $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('volume', ltrim(str_ireplace(['vol.', 'vol'], '', $search)))
                    ->when($this->parsePeriod($search), fn ($q, $period) => $q->orWhere('period', $period)));
            })
            ->when($request->integer('content_category_id'), fn ($q, $id) => $q->where('content_category_id', $id))
            ->orderByDesc('period')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.themes.index', [
            'themes' => $themes,
            'contentCategories' => ContentCategory::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(ContentCategoryThemeRequest $request): RedirectResponse
    {
        $theme = ContentCategoryTheme::create($request->payload());
        activity()->log('Themes', 'Created theme', $theme, $request->payload());

        return back()->with('success', 'Theme created.');
    }

    public function update(ContentCategoryThemeRequest $request, ContentCategoryTheme $theme): RedirectResponse
    {
        $theme->update($request->payload());
        activity()->log('Themes', 'Updated theme', $theme, $request->payload());

        return back()->with('success', 'Theme updated.');
    }

    public function destroy(ContentCategoryTheme $theme): RedirectResponse
    {
        activity()->log('Themes', 'Deleted theme', $theme, ['name' => $theme->name]);
        $theme->delete();

        return back()->with('success', 'Theme deleted.');
    }

    private function parsePeriod(string $value): ?string
    {
        try {
            return Carbon::createFromFormat('!F Y', ucwords($value))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
