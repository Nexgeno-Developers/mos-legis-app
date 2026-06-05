<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'facilities',
        'thumbnail',
        'images',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function cabins()
    {
        return $this->hasMany(Cabin::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
