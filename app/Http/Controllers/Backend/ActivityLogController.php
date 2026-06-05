<?php

namespace App\Http\Controllers\Backend;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends BaseController
{
    protected $module;

    public function __construct()
    {
        $this->module = 'activity-logs';
        view()->share('module', $this->module);
        view()->share('moduleName', 'Activity Logs');

        $this->middleware('permission:activity-logs view')->only(['index']);
        $this->middleware('permission:activity-logs delete')->only(['clearLast30Days']);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $module = $request->input('module');
        $action = $request->input('action');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = ActivityLog::with('user')->orderByDesc('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('module', 'like', '%'.$search.'%')
                    ->orWhere('action', 'like', '%'.$search.'%')
                    ->orWhere('remarks', 'like', '%'.$search.'%')
                    ->orWhere('ip_address', 'like', '%'.$search.'%')
                    ->orWhere('record_id', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($module) {
            $query->where('module', $module);
        }

        if ($action) {
            $query->where('action', $action);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $pageData = $query->paginate(config('custom.pagination_per_page'))
            ->appends($request->query());

        $modules = ActivityLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('backend.activity-logs.index', compact(
            'pageData',
            'modules',
            'actions',
            'search',
            'module',
            'action',
            'dateFrom',
            'dateTo'
        ));
    }

    public function clearLast30Days(Request $request)
    {
        try {
            $deleted = ActivityLog::where('created_at', '<', now()->subDays(30))->delete();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'status' => true,
                    'notification' => $deleted.' log(s) from the last 30 days cleared successfully.',
                ]);
            }

            return redirect()->route('activity-logs.index')
                ->with('success', $deleted.' log(s) from the last 30 days cleared successfully.');
        } catch (\Exception $e) {
            Log::error('Error clearing activity logs', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'notification' => 'There was an error clearing the logs.',
                ]);
            }

            return redirect()->route('activity-logs.index')
                ->with('error', 'There was an error clearing the logs.');
        }
    }
}
