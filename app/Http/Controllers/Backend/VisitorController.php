<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = 'Visitors';
        view()->share('moduleName', $this->moduleName);
    }

    public function index()
    {
        $search = request()->input('search');
        $query = Visitor::query();

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('ip_address', 'like', '%'.$search.'%')
                    ->orWhere('url', 'like', '%'.$search.'%')
                    ->orWhere('referrer', 'like', '%'.$search.'%')
                    ->orWhere('device_type', 'like', '%'.$search.'%')
                    ->orWhere('browser', 'like', '%'.$search.'%')
                    ->orWhere('platform', 'like', '%'.$search.'%');
            });
        }

        $query->orderBy('id', 'desc');
        $pageData = $query->paginate(config('custom.pagination_per_page'));

        return view('backend.visitors.index', compact('pageData'));
    }

    public function destroy($id)
    {
        try {
            Visitor::destroy($id);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => true, 'notification' => 'Record deleted successfully!']);
            }

            return redirect()->route('visitors.index')->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting Visitor record', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'visitor_id' => $id,
            ]);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => false, 'notification' => 'There was an error deleting the record.']);
            }

            return redirect()->route('visitors.index')->with('error', 'There was an error deleting the record.');
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = explode(',', $request->input('ids'));

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['status' => false, 'notification' => 'No items selected for deletion.']);
            }

            $deleted = Visitor::whereIn('id', $ids)->delete();

            if ($deleted > 0) {
                return response()->json([
                    'status' => true,
                    'notification' => $deleted.' record(s) deleted successfully!',
                ]);
            }

            return response()->json(['status' => false, 'notification' => 'No records were deleted.']);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting Visitor records', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'ids' => $request->input('ids'),
            ]);

            return response()->json(['status' => false, 'notification' => 'There was an error deleting the records.']);
        }
    }
}