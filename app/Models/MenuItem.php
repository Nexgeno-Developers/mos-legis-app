<?php

namespace App\Models;

use App\Enums\MenuLinkType;
use App\Enums\PublishStatus;
use App\Enums\RecordStatus;
use App\Models\Concerns\HasRecordStatus;
use App\Support\SiteMenu;
use App\Support\SiteRoutes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One entry in a website menu: a link (website page, CMS page, custom URL)
 * or a group heading whose links render as a dropdown (header) or a column (footer).
 */
#[Fillable(['menu_id', 'parent_id', 'label', 'link_type', 'route_name', 'page_id', 'url', 'open_in_new_tab', 'status', 'sort_order'])]
class MenuItem extends Model
{
    use HasRecordStatus;

    protected static function booted(): void
    {
        static::saved(fn () => SiteMenu::flush());
        static::deleted(fn () => SiteMenu::flush());
    }

    protected function casts(): array
    {
        return [
            'link_type' => MenuLinkType::class,
            'status' => RecordStatus::class,
            'open_in_new_tab' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function isGroup(): bool
    {
        return $this->link_type === MenuLinkType::None;
    }

    /** Where the item points, for the admin list. */
    public function destination(): string
    {
        return match ($this->link_type) {
            MenuLinkType::Route => SiteRoutes::label($this->route_name) ?? 'Unknown page — hidden',
            MenuLinkType::Page => match (true) {
                $this->page === null => 'Deleted page — hidden',
                $this->page->status !== PublishStatus::Published => $this->page->title.' (draft — hidden)',
                default => $this->page->title,
            },
            MenuLinkType::Url => (string) $this->url,
            MenuLinkType::None => trans_choice('{0} No links yet — hidden|{1} :count link|[2,*] :count links', $this->children->count()),
        };
    }
}
