<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'property_id',
        'cabin_id',
        'seat_id',
        'occupant_name',
        'occupant_phone',
        'occupant_id_proof_no',
        'amount',
        'kyc_status',
    ];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'property_id' => 'integer',
            'cabin_id' => 'integer',
            'seat_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function cabin(): BelongsTo
    {
        return $this->belongsTo(Cabin::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }
}
