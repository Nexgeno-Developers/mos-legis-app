<?php

use App\Models\ActivityLog;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks — run `php artisan schedule:work` locally, or a cron entry
| `* * * * * php /path/to/artisan schedule:run` in production.
|--------------------------------------------------------------------------
*/

// SOW A.20: delete activity logs older than 30 days.
Schedule::command('model:prune', ['--model' => [ActivityLog::class]])->dailyAt('01:00');

// Expired/used OTP codes are no longer needed.
Schedule::call(fn () => OtpVerification::where('expires_at', '<', now()->subDay())->delete())
    ->name('prune-otp-verifications')
    ->dailyAt('01:15');
