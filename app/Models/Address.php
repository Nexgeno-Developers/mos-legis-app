<?php

namespace App\Models;

use App\Enums\TaxIdType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The single saved billing address of a user (clarification #5). Payments
 * keep their own frozen copy via toBillingSnapshot().
 */
#[Fillable([
    'user_id', 'recipient_name', 'organization_name', 'phone', 'address_line1', 'address_line2',
    'country_code', 'state', 'city', 'postal_code', 'tax_id_type', 'tax_id_number',
])]
class Address extends Model
{
    protected function casts(): array
    {
        return ['tax_id_type' => TaxIdType::class];
    }

    protected static function booted(): void
    {
        // Only the number is asked for; its kind follows the country.
        static::saving(function (Address $address) {
            $address->tax_id_type = blank($address->tax_id_number)
                ? TaxIdType::None
                : (strtoupper((string) $address->country_code) === 'IN' ? TaxIdType::Gst : TaxIdType::Vat);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string|null> */
    public function toBillingSnapshot(): array
    {
        return [
            'name' => $this->recipient_name,
            'organization_name' => $this->organization_name,
            'phone' => $this->phone,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'state' => $this->state,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'tax_id_type' => $this->tax_id_type?->value,
            'tax_id_number' => $this->tax_id_number,
        ];
    }
}
