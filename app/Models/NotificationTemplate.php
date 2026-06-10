<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'sms_template',
        'whatsapp_template',
        'email_subject',
        'email_template',
        'sms_enabled',
        'whatsapp_enabled',
        'email_enabled',
        'status',
    ];

    protected $casts = [
        'sms_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'status' => 'boolean',
    ];
}
