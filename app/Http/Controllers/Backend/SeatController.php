<?php

namespace App\Http\Controllers\Backend;

use App\Models\Cabin;
use App\Models\Property;
use App\Models\Seat;
use App\Models\SeatPricing;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;

class SeatController extends BaseController
{
    protected $module;

    public function __construct()
    {
        $this->module = 'seats';
        view()->share('module', $this->module);

        $this->middleware('permission:seats view')->only(['index', 'show']);
        $this->middleware('permission:seats create')->only(['create', 'store']);
        $this->middleware('permission:seats edit')->only(['edit', 'update']);
        $this->middleware('permission:seats delete')->only(['destroy']);
    }

    public function index()
    {
        $search = request()->input('search');
        $propertyId = request()->input('property_id');
        $cabinId = request()->input('cabin_id');

        $pageData = Seat::with(['property', 'cabin', 'pricing'])
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->when($cabinId, fn ($query) => $query->where('cabin_id', $cabinId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('seat_no', 'like', '%'.$search.'%')
                        ->orWhereHas('property', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('cabin', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $properties = Property::orderBy('name')->get(['id', 'name']);
        $cabins = Cabin::when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->orderBy('name')
            ->get(['id', 'name', 'property_id']);

        $allCabins = Cabin::orderBy('name')->get(['id', 'name', 'property_id']);

        return view('backend.'.$this->module.'.index', compact('pageData', 'properties', 'cabins', 'allCabins'));
    }

    public function create()
    {
        $properties = Property::orderBy('name')->get(['id', 'name']);
        $cabins = Cabin::orderBy('name')->get(['id', 'name', 'property_id']);
        $selectedPropertyId = request()->input('property_id');
        $selectedCabinId = request()->input('cabin_id');

        return view('backend.'.$this->module.'.create', compact(
            'properties',
            'cabins',
            'selectedPropertyId',
            'selectedCabinId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate($this->validationRules($request));

        try {
            $seat = Seat::create([
                'property_id' => $request->property_id,
                'cabin_id' => $request->cabin_id,
                'seat_no' => $request->seat_no,
                'status' => $request->status,
            ]);

            $this->syncPricing($seat, $request->input('pricing', []));

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
        $seat = Seat::with('pricing')->findOrFail($id);
        $properties = Property::orderBy('name')->get(['id', 'name']);
        $cabins = Cabin::orderBy('name')->get(['id', 'name', 'property_id']);

        $pricing = $seat->pricing->pluck('price', 'duration');

        return view('backend.'.$this->module.'.edit', compact('seat', 'properties', 'cabins', 'pricing'));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->validationRules($request));

        try {
            $seat = Seat::findOrFail($id);
            $seat->update([
                'property_id' => $request->property_id,
                'cabin_id' => $request->cabin_id,
                'seat_no' => $request->seat_no,
                'status' => $request->status,
            ]);

            $this->syncPricing($seat, $request->input('pricing', []));

            return response()->json(['status' => true, 'notification' => __('messages.updated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    public function destroy($id)
    {
        try {
            Seat::destroy($id);

            return redirect()->route($this->module.'.index')->with('success', __('messages.deleted'));
        } catch (\Exception $e) {
            return redirect()->route($this->module.'.index')->with('error', __('messages.failed'));
        }
    }

    protected function validationRules(Request $request): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'cabin_id' => [
                'required',
                Rule::exists('cabins', 'id')->where('property_id', $request->property_id),
            ],
            'seat_no' => 'required|string|max:50',
            'status' => 'required|boolean',
            'pricing.daily' => 'required|numeric|min:0',
            'pricing.monthly' => 'required|numeric|min:0',
            'pricing.yearly' => 'required|numeric|min:0',
        ];
    }

    protected function syncPricing(Seat $seat, array $pricing): void
    {
        foreach (['daily', 'monthly', 'yearly'] as $duration) {
            if (isset($pricing[$duration]) && $pricing[$duration] !== '') {
                SeatPricing::updateOrCreate(
                    ['seat_id' => $seat->id, 'duration' => $duration],
                    ['price' => $pricing[$duration]]
                );
            }
        }
    }
}
