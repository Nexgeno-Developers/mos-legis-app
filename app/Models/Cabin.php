<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cabin extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'type',
        'thumbnail',
        'images',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'status' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
