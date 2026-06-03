<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id',
        'detail_key',
        'detail_value',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
