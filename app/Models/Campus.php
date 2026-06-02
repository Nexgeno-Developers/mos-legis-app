<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    protected $fillable = [
        'name',
        'description',
        'gallery',
        'is_active',
        'company_id',
        'series',
    ];
}
