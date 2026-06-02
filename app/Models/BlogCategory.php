<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'meta_title',
        'meta_description',
        'is_active',
        'company_id',
    ];

    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_blog_category', 'blog_category_id', 'blog_id');
    }
}
