<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Awards\SaveBestPaperAward;
use App\Enums\ManuscriptStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BestPaperAwardRequest;
use App\Models\BestPaperAward;
use App\Models\ContentCategory;
use App\Models\ManuscriptSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

/**
 * Best Paper Awards: choose the winning published manuscript for each quarter. One winner per
 * quarter; the winner is shown on the Best Paper page and the home page.
 */
class BestPaperAwardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:submissions.best-paper')];
    }

    public function index(Request $request): View
    {
        $awards = BestPaperAward::query()
            ->with(['submission:id,title,user_id,content_category_id', 'submission.author:id,name', 'submission.contentCategory:id,name', 'selector:id,name'])
            ->when($request->integer('year'), fn ($q, $year) => $q->where('award_year', $year))
            ->when($request->integer('category'), fn ($q, $id) => $q->whereHas('submission', fn ($q) => $q->where('content_category_id', $id)))
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->whereHas('submission', fn ($q) => $q
                ->where('title', 'like', "%{$search}%")->orWhere('id', ltrim(preg_replace('/\D/', '', $search), '0') ?: 0)))
            ->orderByDesc('award_year')
            ->orderByDesc('award_quarter')
            ->paginate(20)
            ->withQueryString();

        [$quarter, $quarterYear] = BestPaperAward::lastQuarter();

        return view('admin.best-paper-awards.index', [
            'awards' => $awards,
            'years' => BestPaperAward::query()->distinct()->orderByDesc('award_year')->pluck('award_year', 'award_year'),
            'categories' => ContentCategory::orderBy('name')->pluck('name', 'id'),
            // "Who won last quarter?" at a glance.
            'lastQuarter' => [
                'period' => "{$quarter} {$quarterYear}",
                'query' => ['award_quarter' => $quarter, 'award_year' => $quarterYear],
                'award' => BestPaperAward::with('submission:id,title')->where(['award_quarter' => $quarter, 'award_year' => $quarterYear])->first(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        [$quarter, $year] = BestPaperAward::lastQuarter();

        return $this->form(new BestPaperAward([
            'manuscript_submission_id' => $request->integer('submission') ?: null,
            'award_quarter' => $request->string('award_quarter')->value() ?: $quarter,
            'award_year' => $request->integer('award_year') ?: $year,
        ]));
    }

    public function store(BestPaperAwardRequest $request, SaveBestPaperAward $save): RedirectResponse
    {
        $award = $save->handle($request->validated(), $request->user());

        return redirect()->route('admin.best-paper-awards.index')->with('success', "Best Paper winner saved for {$award->periodLabel()}. The author has been notified.");
    }

    public function edit(BestPaperAward $bestPaperAward): View
    {
        return $this->form($bestPaperAward);
    }

    public function update(BestPaperAwardRequest $request, BestPaperAward $bestPaperAward, SaveBestPaperAward $save): RedirectResponse
    {
        $award = $save->handle($request->validated(), $request->user(), $bestPaperAward);

        return redirect()->route('admin.best-paper-awards.index')->with('success', "Best Paper award for {$award->periodLabel()} updated.");
    }

    public function destroy(BestPaperAward $bestPaperAward, SaveBestPaperAward $save): RedirectResponse
    {
        $save->delete($bestPaperAward);

        return back()->with('success', 'Award removed.');
    }

    private function form(BestPaperAward $award): View
    {
        // Published manuscripts to choose from (newest first), labelled "MOS-00012 — Title · Author".
        $manuscripts = ManuscriptSubmission::where('stage', ManuscriptStage::Published)
            ->with('author:id,name')
            ->latest('published_at')
            ->get(['id', 'title', 'user_id', 'published_at'])
            ->mapWithKeys(fn (ManuscriptSubmission $m) => [$m->id => $m->reference().' — '.$m->title.' · '.$m->author?->name]);

        return view('admin.best-paper-awards.form', ['award' => $award, 'manuscripts' => $manuscripts]);
    }
}
