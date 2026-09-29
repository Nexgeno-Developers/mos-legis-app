<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * SOW A.20 — audit trail with search/filter and deletion of logs older than 30 days
 * (also pruned automatically every day by the scheduler).
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('activity-logs.view');

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where('remarks', 'like', "%{$search}%"))
            ->when($request->string('module')->value(), fn ($q, $module) => $q->where('module', $module))
            ->when($request->string('action')->trim()->value(), fn ($q, $action) => $q->where('action', 'like', "%{$action}%"))
            ->when($request->date('from'), fn ($q, $from) => $q->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($q, $to) => $q->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'modules' => ActivityLog::query()->distinct()->orderBy('module')->pluck('module', 'module'),
            'staleCount' => ActivityLog::where('created_at', '<', now()->subDays(ActivityLog::RETENTION_DAYS))->count(),
        ]);
    }

    public function purge(): RedirectResponse
    {
        Gate::authorize('activity-logs.delete');

        $deleted = ActivityLog::where('created_at', '<', now()->subDays(ActivityLog::RETENTION_DAYS))->delete();

        activity()->log('Activity Logs', 'Deleted logs older than '.ActivityLog::RETENTION_DAYS.' days', null, ['deleted' => $deleted]);

        return back()->with('success', "Deleted {$deleted} log entries older than ".ActivityLog::RETENTION_DAYS.' days.');
    }
}
