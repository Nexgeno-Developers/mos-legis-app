<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageMeta extends Model
{
    //use HasFactory;

    protected $fillable = [
        'page_id',
        'meta_key',
        'meta_value',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
