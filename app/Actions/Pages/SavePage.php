<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Support\GoogleMap;
use App\Support\Html;
use App\Support\PageTemplates;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Saves a CMS page and its template-specific metas (SOW A.11).
 */
class SavePage
{
    /** @param array<string, mixed> $data validated PageRequest data */
    public function handle(array $data, ?Page $page = null, ?int $editorId = null): Page
    {
        return DB::transaction(function () use ($data, $page, $editorId) {
            $page ??= new Page(['template' => $data['template']]);

            $page->fill([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'status' => $data['status'],
                'excerpt' => $data['excerpt'] ?? null,
                'content' => Html::clean($data['content'] ?? null),
                'seo_title' => $data['seo_title'] ?? null,
                'seo_description' => $data['seo_description'] ?? null,
                'updated_by' => $editorId,
            ]);

            foreach (['featured_image', 'og_image'] as $imageField) {
                if (($data[$imageField] ?? null) instanceof UploadedFile) {
                    if ($page->{$imageField}) {
                        Storage::disk('public')->delete($page->{$imageField});
                    }
                    $page->{$imageField} = $data[$imageField]->store('pages', 'public');
                }
            }

            $page->save();

            foreach (PageTemplates::fields($page->template) as $key => $field) {
                $value = $data['meta'][$key] ?? null;

                if ($field['type'] === 'map') {
                    // Store just the embed URL, even when the full <iframe> snippet was pasted.
                    $value = GoogleMap::embedUrl($value);
                }

                if ($field['type'] === 'repeater') {
                    // Drop rows where every column is blank (e.g. the empty row the editor starts with).
                    $rows = collect($value ?? [])
                        ->map(fn ($row) => array_map(fn ($v) => $v === null ? '' : trim((string) $v), array_intersect_key((array) $row, $field['columns'])))
                        ->filter(fn ($row) => implode('', $row) !== '')
                        ->values()
                        ->all();
                    $value = json_encode($rows, JSON_UNESCAPED_UNICODE);
                }

                $page->metas()->updateOrCreate(
                    ['meta_key' => $key],
                    ['meta_value' => $value, 'meta_type' => PageTemplates::metaType($field)],
                );
            }

            return $page;
        });
    }
}
