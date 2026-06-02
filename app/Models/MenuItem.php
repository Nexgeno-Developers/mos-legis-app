<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_group_id',
        'parent_id',
        'name',
        'url',
        'icon',
        'order',
        'status',
    ];

    /**
     * Get the menu group this item belongs to
     */
    public function menuGroup(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class);
    }

    /**
     * Get the parent menu item
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get all child menu items
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Check if this menu item has children
     */
    public function hasChildren(): bool
    {
        return $this->children->count() > 0;
    }

    /**
     * Get all active menu items
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get menu tree with children
     */
    public static function getMenuTree($menuGroupSlug)
    {
        $menuGroup = MenuGroup::where('slug', $menuGroupSlug)->where('status', true)->first();
        
        if (!$menuGroup) {
            return [];
        }

        return self::with('children')
            ->where('menu_group_id', $menuGroup->id)
            ->where('parent_id', null)
            ->active()
            ->orderBy('order')
            ->get();
    }
}
