<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'bio',
        'profile_image',
        'is_active',
        'company_id',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
