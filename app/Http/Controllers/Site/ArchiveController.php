<?php

namespace App\Http\Controllers\Site;

use App\Enums\ManuscriptStage;
use App\Enums\PageTemplate;
use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\ContentCategory;
use App\Models\ManuscriptSubmission;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * SOW C.01 — Journal Archive of published manuscripts: category sidebar, title/author/keyword
 * search, per-article download and a ZIP of everything matching the filters.
 */
class ArchiveController extends Controller
{
    private const ZIP_LIMIT = 200;

    public function index(Request $request): View
    {
        // Title, intro, content and SEO come from Admin → Pages ("Journal archive" template).
        $page = Page::with('metas')->where('template', PageTemplate::Archive)->oldest('id')->first();
        abort_unless($page && $page->status === PublishStatus::Published, 404);

        return view('site.archive.index', [
            'page' => $page,
            'submissions' => $this->filtered($request)
                ->with(['author:id,name', 'contentCategory:id,name', 'theme', 'awards:id,manuscript_submission_id'])
                ->tap(fn ($q) => match ($request->string('sort')->value()) {
                    'oldest' => $q->oldest('published_at'),
                    'title' => $q->orderBy('title'),
                    default => $q->latest('published_at'),
                })
                ->paginate(12)
                ->withQueryString(),
            'categories' => ContentCategory::withCount(['submissions' => fn ($q) => $q->where('stage', ManuscriptStage::Published)])
                ->orderBy('name')->get(),
            'years' => ManuscriptSubmission::published()->whereNotNull('published_at')
                ->selectRaw('YEAR(published_at) as year')->distinct()->orderByDesc('year')->pluck('year'),
        ]);
    }

    public function show(ManuscriptSubmission $submission): View
    {
        abort_unless($submission->stage === ManuscriptStage::Published, 404);

        $submission->load(['author:id,name', 'author.authorProfile', 'contentCategory', 'theme', 'awards']);

        return view('site.archive.show', [
            'submission' => $submission,
            'related' => ManuscriptSubmission::published()->where('content_category_id', $submission->content_category_id)
                ->whereKeyNot($submission->id)->with('author:id,name')->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function download(ManuscriptSubmission $submission): StreamedResponse
    {
        abort_unless($submission->stage === ManuscriptStage::Published && Storage::disk('local')->exists($submission->manuscript_attachment), 404);

        return Storage::disk('local')->download($submission->manuscript_attachment, $this->fileName($submission));
    }

    public function zip(Request $request): BinaryFileResponse
    {
        $submissions = $this->filtered($request)->latest('published_at')->limit(self::ZIP_LIMIT)->get(['id', 'title', 'manuscript_attachment']);
        abort_if($submissions->isEmpty(), 404, 'No published manuscripts match these filters.');

        $path = tempnam(sys_get_temp_dir(), 'archive');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);

        $added = 0;
        foreach ($submissions as $submission) {
            $file = Storage::disk('local')->path($submission->manuscript_attachment);
            if (is_file($file)) {
                $zip->addFile($file, $this->fileName($submission));
                $added++;
            }
        }
        $zip->close();

        // An archive with no files is never written to disk; say so instead of failing.
        if ($added === 0) {
            @unlink($path);
            abort(404, 'The manuscript files for these filters are not available for download.');
        }

        return response()->download($path, 'mos-legis-archive-'.now()->format('Ymd').'.zip')->deleteFileAfterSend();
    }

    private function filtered(Request $request): Builder
    {
        return ManuscriptSubmission::query()
            ->published()
            ->when($request->integer('category'), fn ($q, $id) => $q->where('content_category_id', $id))
            ->when($request->integer('year'), fn ($q, $year) => $q->whereYear('published_at', $year))
            // One search box: title, author / co-authors or keyword.
            ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhereHas('author', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                ->orWhere('co_authors', 'like', "%{$term}%")
                ->orWhere('keywords', 'like', '%'.addcslashes($term, '%_').'%')))
            ->when($request->string('title')->trim()->value(), fn ($q, $title) => $q->where('title', 'like', "%{$title}%"))
            ->when($request->string('author')->trim()->value(), fn ($q, $author) => $q->where(fn ($q) => $q
                ->whereHas('author', fn ($q) => $q->where('name', 'like', "%{$author}%"))
                ->orWhere('co_authors', 'like', "%{$author}%")))
            ->when($request->string('keyword')->trim()->value(), fn ($q, $keyword) => $q->where('keywords', 'like', '%'.addcslashes($keyword, '%_').'%'));
    }

    private function fileName(ManuscriptSubmission $submission): string
    {
        return $submission->reference().'-'.Str::slug(Str::limit($submission->title, 60, '')).'.docx';
    }
}
