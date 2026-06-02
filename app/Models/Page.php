<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    //use HasFactory;

    protected $fillable = [
        'slug',
        'language',
        'title',
        'content',
        'is_active',
        'layout',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_schema',
        'company_id',
    ];

    public function meta()
    {
        return $this->hasMany(PageMeta::class);
    }
}
