<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'slug',
        'language',
        'title',
        'content',
        'featured_image',
        'layout',
        'is_active',
        'company_id',
        'author_id',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_schema',
        'published_at',
        'auto_slug',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_slug' => 'array',
    ];

    public function meta()
    {
        return $this->hasMany(PostMeta::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'post_category', 'post_id', 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }
}
