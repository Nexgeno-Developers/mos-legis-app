<?php

namespace App\Models;

use App\Enums\MenuLocation;
use App\Support\SiteMenu;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A website menu rendered at a fixed location (header, footer).
 */
#[Fillable(['name', 'location'])]
class Menu extends Model
{
    protected static function booted(): void
    {
        static::saved(fn () => SiteMenu::flush());
        static::deleted(fn () => SiteMenu::flush());
    }

    protected function casts(): array
    {
        return ['location' => MenuLocation::class];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Top-level items with their children, in display order. */
    public function tree(): HasMany
    {
        return $this->items()->whereNull('parent_id')->with(['children.page', 'page']);
    }

    public static function forLocation(MenuLocation $location): self
    {
        return static::firstOrCreate(['location' => $location], ['name' => $location->label()]);
    }
}
