<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'logo', 'email', 'phone', 'whatsapp',
        'address', 'website', 'google_map', 'is_active',
        'footer_logo_image', 'short_description',
    ];

    public function meta()
    {
        return $this->hasMany(CompanyMeta::class);
    }
}
