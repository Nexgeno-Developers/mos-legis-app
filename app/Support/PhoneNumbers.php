<?php

namespace App\Support;

use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Throwable;

/**
 * Phone numbers are entered with a country picker (intl-tel-input) and stored in
 * international E.164 format, e.g. +919876543210, so SMS/WhatsApp and Razorpay get a
 * complete number. A number typed without a country code is read as the default country.
 */
final class PhoneNumbers
{
    /** ISO code of Settings → General → Default Country (falls back to India). */
    public static function defaultCountry(): string
    {
        $code = array_search(settings('general.default_country'), config('countries'), true);

        return is_string($code) ? $code : 'IN';
    }

    /** @return list<mixed> */
    public static function rules(bool $required = false): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:20', (new Phone)->international()->country(self::defaultCountry())];
    }

    /** E.164 for a valid number; the input unchanged otherwise (so validation can reject it). */
    public static function normalize(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return is_string($value) ? null : $value;
        }

        try {
            $number = new PhoneNumber($value, self::defaultCountry());

            return $number->isValid() ? $number->formatE164() : $value;
        } catch (Throwable) {
            return $value;
        }
    }

    /** Readable international format for display, e.g. +91 98765 43210. */
    public static function display(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return (new PhoneNumber($value, self::defaultCountry()))->formatInternational();
        } catch (Throwable) {
            return $value;
        }
    }

    public const MESSAGE = 'Enter a valid phone number for the selected country.';
}
