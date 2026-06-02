<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinyMCEKey extends Model
{
    protected $table = 'plugin_tinymce_keys'; // ✅ explicitly set table name

    protected $fillable = ['api_key', 'month', 'year', 'count'];
}
