<?php

namespace App\Models;

use App\Enums\BlogStatus;
use App\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SOW A.05 / B.05 / C.02 — one table for superadmin and author blog posts, scoped by user_id.
 */
#[Fillable([
    'user_id', 'blog_title', 'slug', 'category_id', 'author_name', 'featured_image', 'excerpt', 'content',
    'meta_title', 'meta_description', 'og_image', 'status', 'publish_date', 'featured_post',
])]
class Blog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => BlogStatus::class,
            'publish_date' => 'date',
            'featured_post' => 'boolean',
            'views' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_tag_pivot', 'blog_id', 'blog_tag_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', CommentStatus::Approved);
    }

    /** Publicly visible: published and not scheduled for a future date. */
    #[Scope]
    protected function live(Builder $query): void
    {
        $query->where('status', BlogStatus::Published)->whereDate('publish_date', '<=', today());
    }
}
