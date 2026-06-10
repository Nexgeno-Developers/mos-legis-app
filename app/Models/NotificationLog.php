<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'template_slug',
        'channel',
        'provider',
        'recipient',
        'message',
        'status',
        'response',
        'payload',
    ];

    protected $casts = [
        'status' => 'boolean',
        'payload' => 'array',
    ];
}
