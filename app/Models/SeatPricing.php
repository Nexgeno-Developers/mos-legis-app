<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatPricing extends Model
{
    use HasFactory;

    protected $table = 'seat_pricing';

    protected $fillable = [
        'seat_id',
        'duration',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'seat_id' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }
}
