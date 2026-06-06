<?php

namespace App\Http\Controllers\Backend;

use App\Models\Cabin;
use App\Models\Property;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class CabinController extends BaseController
{
    protected $module;

    public function __construct()
    {
        $this->module = 'cabins';
        view()->share('module', $this->module);

        $this->middleware('permission:cabins view')->only(['index', 'show']);
        $this->middleware('permission:cabins create')->only(['create', 'store']);
        $this->middleware('permission:cabins edit')->only(['edit', 'update']);
        $this->middleware('permission:cabins delete')->only(['destroy']);
    }

    public function index()
    {
        $search = request()->input('search');
        $propertyId = request()->input('property_id');

        $pageData = Cabin::with('property')
            ->withSum([
                'seatPricing as monthly_total' => fn ($query) => $query->where('seat_pricing.duration', 'monthly'),
            ], 'price')
            ->withSum([
                'seatPricing as yearly_total' => fn ($query) => $query->where('seat_pricing.duration', 'yearly'),
            ], 'price')
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('property', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $properties = Property::orderBy('name')->get(['id', 'name']);

        return view('backend.'.$this->module.'.index', compact('pageData', 'properties'));
    }

    public function create()
    {
        $properties = Property::orderBy('name')->get(['id', 'name']);

        return view('backend.'.$this->module.'.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|min:3|max:100',
            'type' => 'nullable|in:flexible,private,shared',
            'thumbnail' => 'required|string',
            'images' => 'required|string',
            'status' => 'required|boolean',
        ]);

        try {
            $cabin = Cabin::create([
                'property_id' => $request->property_id,
                'name' => $request->name,
                'type' => $request->input('type', 'flexible'),
                'thumbnail' => $request->thumbnail,
                'images' => $request->images,
                'status' => $request->status,
            ]);

            ActivityLogService::store('cabins', 'create', (int) $cabin->id, $request->all(), 'Cabin created');

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
        $cabin = Cabin::findOrFail($id);
        $properties = Property::orderBy('name')->get(['id', 'name']);

        return view('backend.'.$this->module.'.edit', compact('cabin', 'properties'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|min:3|max:100',
            'type' => 'nullable|in:flexible,private,shared',
            'thumbnail' => 'required|string',
            'images' => 'required|string',
            'status' => 'required|boolean',
        ]);

        try {
            $cabin = Cabin::findOrFail($id);
            $cabin->update([
                'property_id' => $request->property_id,
                'name' => $request->name,
                'type' => $request->input('type', 'flexible'),
                'thumbnail' => $request->thumbnail,
                'images' => $request->images,
                'status' => $request->status,
            ]);

            ActivityLogService::store('cabins', 'update', (int) $cabin->id, $request->all(), 'Cabin updated');

            return response()->json(['status' => true, 'notification' => __('messages.updated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    public function destroy($id)
    {
        try {
            $cabin = Cabin::findOrFail($id);

            ActivityLogService::store('cabins', 'delete', (int) $cabin->id, ['name' => $cabin->name, 'property_id' => $cabin->property_id], 'Cabin deleted');

            $cabin->delete();

            return response()->json(['status' => true, 'notification' => __('messages.deleted')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }
}
