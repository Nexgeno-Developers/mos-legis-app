<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'gallery',
        'year',
        'is_active',
        'company_id',
    ];
}
