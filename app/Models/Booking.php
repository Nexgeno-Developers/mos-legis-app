<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'invoice_no',
        'user_id',
        'start_datetime',
        'end_datetime',
        'subtotal_amount',
        'tax_rate',
        'tax_amount',
        'grand_total_amount',
        'duration_type',
        'booking_status',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'subtotal_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payable_id')
            ->where('payable_type', 'booking');
    }

    /**
     * @return array<int, string>
     */
    public function cabinsAndSeatsLines(): array
    {
        $lines = [];
        $index = 1;

        foreach ($this->items->groupBy('cabin_id') as $items) {
            $cabinName = $items->first()->cabin?->name ?? '—';
            $seatNos = $items
                ->map(fn (BookingItem $item) => $item->seat?->seat_no)
                ->filter()
                ->unique()
                ->values()
                ->implode(',');

            //$lines[] = $index.'. '.$cabinName.' · '.$seatNos;
            $lines[] = $cabinName.' · '.$seatNos;
            $index++;
        }

        return $lines;
    }

    public function propertyNamesDisplay(): string
    {
        return $this->items
            ->map(fn (BookingItem $item) => $item->property?->name)
            ->filter()
            ->unique()
            ->implode(', ');
    }
}
