<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes the SOW A.20 audit trail. Call from controllers/actions after a successful change.
 */
class ActivityLogger
{
    /** Keys never written to the payload. */
    private const REDACTED = ['password', 'password_confirmation', 'otp', 'otp_code', 'remember_token', '_token'];

    public function log(string $module, string $action, Model|int|string|null $record = null, array $payload = [], ?string $remarks = null): ActivityLog
    {
        $request = app()->runningInConsole() ? null : request();

        return ActivityLog::create([
            'user_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'record_id' => $record instanceof Model ? (string) $record->getKey() : ($record !== null ? (string) $record : null),
            'payload' => $payload ? array_diff_key($payload, array_flip(self::REDACTED)) : null,
            'remarks' => $remarks,
            'ip_address' => $request?->ip(),
        ]);
    }
}
