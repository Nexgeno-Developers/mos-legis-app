<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'cabin_id',
        'seat_no',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'cabin_id' => 'integer',
            'status' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function cabin(): BelongsTo
    {
        return $this->belongsTo(Cabin::class);
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(SeatPricing::class);
    }

    public function priceFor(string $duration): ?string
    {
        $pricing = $this->pricing->firstWhere('duration', $duration);

        return $pricing ? formatCurrency($pricing->price) : null;
    }
}
