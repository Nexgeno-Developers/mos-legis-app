<?php

use App\Support\ActivityLogger;
use App\Support\Settings;
use Illuminate\Support\Carbon;

if (! function_exists('settings')) {
    /** Read a cached setting, e.g. settings('payment.currency_symbol'). */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $settings = app(Settings::class);

        return $key === null ? $settings : $settings->get($key, $default);
    }
}

if (! function_exists('activity')) {
    function activity(): ActivityLogger
    {
        return app(ActivityLogger::class);
    }
}

if (! function_exists('money')) {
    /** Format an INR amount with the configured symbol, e.g. ₹1,250.00. */
    function money(float|int|string|null $amount): string
    {
        return settings('payment.currency_symbol', '₹').number_format((float) $amount, 2);
    }
}

if (! function_exists('format_date')) {
    /** Format a date using the "Date Format" setting (SOW A.21). */
    function format_date(DateTimeInterface|string|null $date, bool $withTime = false): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $format = match (settings('general.date_format')) {
            'MM-DD-YYYY' => 'm-d-Y',
            'YYYY-MM-DD' => 'Y-m-d',
            'DD MMM YYYY' => 'd M Y',
            default => 'd-m-Y',
        };

        return Carbon::parse($date)->format($withTime ? $format.' H:i' : $format);
    }
}
