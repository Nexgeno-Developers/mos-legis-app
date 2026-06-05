<?php

namespace App\Http\Controllers\Backend;

use App\Models\Property;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class PropertyController extends BaseController
{
    protected $module;

    public function __construct()
    {
        $this->module = 'properties';
        view()->share('module', $this->module);

        $this->middleware('permission:properties view')->only(['index', 'show']);
        $this->middleware('permission:properties create')->only(['create', 'store']);
        $this->middleware('permission:properties edit')->only(['edit', 'update']);
        $this->middleware('permission:properties delete')->only(['destroy']);
    }

    public function index()
    {
        $search = request()->input('search');

        $pageData = Property::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('backend.'.$this->module.'.index', compact('pageData'));
    }

    public function create()
    {
        return view('backend.'.$this->module.'.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:55',
            'address' => 'nullable|string',
            'facilities' => 'nullable|string',
            'thumbnail' => 'required|string',
            'images' => 'required|string',
            'status' => 'required|boolean',
        ]);

        try {
            $property = Property::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'facilities' => $request->facilities,
                'thumbnail' => $request->thumbnail,
                'images' => $request->images,
                'status' => $request->status,
            ]);

            ActivityLogService::store('properties', 'create', (int) $property->id, $request->all(), 'Property created');

            return response()->json(['status' => true, 'notification' => __('messages.created')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $property = Property::findOrFail($id);

        return view('backend.'.$this->module.'.edit', compact('property'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:55',
            'address' => 'nullable|string',
            'facilities' => 'nullable|string',
            'thumbnail' => 'required|string',
            'images' => 'required|string',
            'status' => 'required|boolean',
        ]);

        try {
            $property = Property::findOrFail($id);
            $property->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'facilities' => $request->facilities,
                'thumbnail' => $request->thumbnail,
                'images' => $request->images,
                'status' => $request->status,
            ]);

            ActivityLogService::store('properties', 'update', (int) $property->id, $request->all(), 'Property updated');

            return response()->json(['status' => true, 'notification' => __('messages.updated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    public function destroy($id)
    {
        try {
            $property = Property::findOrFail($id);

            ActivityLogService::store('properties', 'delete', (int) $property->id, ['name' => $property->name, 'email' => $property->email], 'Property deleted');

            $property->delete();

            return redirect()->route($this->module.'.index')->with('success', __('messages.deleted'));
        } catch (\Exception $e) {
            return redirect()->route($this->module.'.index')->with('error', __('messages.failed'));
        }
    }
}
