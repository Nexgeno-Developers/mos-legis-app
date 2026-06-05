<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function seatPricing(): HasManyThrough
    {
        return $this->hasManyThrough(SeatPricing::class, Seat::class);
    }

    public function formattedPriceSum(?float $total): string
    {
        return formatCurrency($total ?? 0);
    }
}
