<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ActivityLogService
{
    public static function store(string $module, string $action, ?int $recordId = null, ?array $payload = null, ?string $remarks = null): void
    {
        try {
            $user = Auth::user();

            ActivityLog::create([
                'user_id' => $user?->id,
                'module' => $module,
                'action' => $action,
                'record_id' => $recordId,
                'payload' => $payload,
                'remarks' => $remarks,
                'ip_address' => Request::capture()->ip(),
            ]);
        } catch (Throwable $e) {
            \Log::error('ActivityLogService: failed to store log', [
                'module' => $module,
                'action' => $action,
                'record_id' => $recordId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}