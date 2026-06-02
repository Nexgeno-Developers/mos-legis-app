<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'url',
        'method',
        'referrer',
        'device_type',
        'browser',
        'platform',
        'company_id',
    ];
}
