<?php

namespace App\Models;

use App\Enums\PageTemplate;
use App\Enums\PublishStatus;
use App\Support\PublicPages;
use App\Support\SiteMenu;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SOW A.11 — common page data here, template-specific data in page_metas.
 */
#[Fillable([
    'title', 'slug', 'content', 'excerpt', 'featured_image', 'status', 'template',
    'seo_title', 'seo_description', 'og_image', 'updated_by',
])]
class Page extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Menus show page titles/links and hide unpublished pages.
        static::saved(fn () => [SiteMenu::flush(), PublicPages::flush()]);
        static::deleted(fn () => [SiteMenu::flush(), PublicPages::flush()]);
    }

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'template' => PageTemplate::class,
        ];
    }

    /** Home is always /; every other page lives at /{slug}. */
    public function url(): string
    {
        return $this->slug === 'home' ? route('home') : url($this->slug);
    }

    /**
     * Pages whose address can't change: Home (/), the Journal Archive (/archive — each manuscript's page
     * lives under /archive/…), Submit, Plagiarism Checker and Contact. Null for every other page.
     */
    public function fixedSlug(): ?string
    {
        return match (true) {
            $this->isHome() => 'home',
            $this->template === \App\Enums\PageTemplate::Archive => 'archive',
            $this->template === \App\Enums\PageTemplate::Submit => 'submit',
            $this->template === \App\Enums\PageTemplate::PlagiarismChecker => 'plagiarism-checker',
            $this->template === \App\Enums\PageTemplate::Contact => 'contact',
            default => null,
        };
    }

    /** Core pages that can't be deleted. */
    public function isProtected(): bool
    {
        return $this->fixedSlug() !== null;
    }

    public function isHome(): bool
    {
        return $this->exists && $this->getOriginal('slug') === 'home';
    }

    public function metas(): HasMany
    {
        return $this->hasMany(PageMeta::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Decoded meta value by key; uses the loaded relation when available. */
    public function meta(string $key, mixed $default = null): mixed
    {
        $meta = $this->metas->firstWhere('meta_key', $key);

        return $meta ? $meta->value() : $default;
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PublishStatus::Published);
    }
}
