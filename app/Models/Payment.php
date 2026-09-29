<?php

namespace App\Models;

use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * SOW A.17 — every pre-screening, publication and standalone plagiarism fee.
 * total_amount is a generated column (amount + tax_amount); never write it.
 */
#[Fillable([
    'user_id', 'payable_type', 'payable_id', 'payment_purpose', 'invoice_number', 'amount', 'tax_amount',
    'currency', 'tax_rate', 'billing_address_id', 'billing_country_code', 'billing_details',
    'payment_method', 'payment_status', 'payment_details', 'gateway_order_id', 'payment_id', 'remarks', 'paid_at',
])]
class Payment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'payment_purpose' => PaymentPurpose::class,
            'payment_status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'billing_details' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function hasTax(): bool
    {
        return (float) $this->tax_amount > 0;
    }

    /** Amount in paise, as Razorpay expects. */
    public function totalInMinorUnits(): int
    {
        return (int) round(((float) $this->amount + (float) $this->tax_amount) * 100);
    }

    public function submissionReference(): ?string
    {
        return $this->payable instanceof ManuscriptSubmission ? $this->payable->reference() : null;
    }
}
