<?php

namespace App\Services\Auth;

use App\Enums\OtpPurpose;
use App\Mail\OtpCodeMail;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * One-time codes stored hashed in otp_verifications, expiring after 10 minutes,
 * limited to 5 attempts; issuing a new code invalidates earlier ones.
 */
class OtpService
{
    public const EXPIRES_MINUTES = 10;

    public function issue(string $email, OtpPurpose $purpose = OtpPurpose::Registration): void
    {
        $code = (string) random_int(100000, 999999);

        OtpVerification::where('identifier', $email)->where('purpose', $purpose)->whereNull('verified_at')->delete();

        OtpVerification::create([
            'identifier' => $email,
            'otp_code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
        ]);

        Mail::to($email)->send(new OtpCodeMail($code, self::EXPIRES_MINUTES));
    }

    public function verify(string $email, string $code, OtpPurpose $purpose = OtpPurpose::Registration): bool
    {
        $otp = OtpVerification::where('identifier', $email)->where('purpose', $purpose)->whereNull('verified_at')->latest('id')->first();

        if (! $otp || ! $otp->isUsable()) {
            return false;
        }

        if (! Hash::check($code, $otp->otp_code)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->forceFill(['verified_at' => now()])->save();

        return true;
    }
}
