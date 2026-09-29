<?php

namespace App\Models;

use App\Enums\OtpPurpose;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['identifier', 'otp_code', 'purpose', 'attempts', 'expires_at', 'verified_at'])]
#[Hidden(['otp_code'])]
class OtpVerification extends Model
{
    public const UPDATED_AT = null;

    public const MAX_ATTEMPTS = 5;

    protected function casts(): array
    {
        return [
            'otp_code' => 'hashed',
            'purpose' => OtpPurpose::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function isUsable(): bool
    {
        return $this->verified_at === null
            && $this->expires_at->isFuture()
            && $this->attempts < self::MAX_ATTEMPTS;
    }
}
