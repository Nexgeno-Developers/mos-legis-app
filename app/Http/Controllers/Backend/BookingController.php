<?php

namespace App\Http\Controllers\Backend;

use App\Models\Booking;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class BookingController extends BaseController
{
    protected $module;

    public function __construct()
    {
        $this->module = 'bookings';
        view()->share('module', $this->module);

        $this->middleware('permission:bookings view')->only(['index', 'show']);
        $this->middleware('permission:bookings create')->only(['create', 'store']);
        $this->middleware('permission:bookings edit')->only(['edit', 'update']);
        $this->middleware('permission:bookings delete')->only(['destroy']);
    }

    public function index()
    {
        $search = request()->input('search');
        $bookingStatus = request()->input('booking_status');
        $paymentStatus = request()->input('payment_status');

        $pageData = Booking::query()
            ->with(['user', 'property', 'items.cabin', 'items.seat'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_no', 'like', '%'.$search.'%')
                        ->orWhere('id', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('property', function ($propertyQuery) use ($search) {
                            $propertyQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($bookingStatus, fn ($query) => $query->where('booking_status', $bookingStatus))
            ->when($paymentStatus, fn ($query) => $query->where('payment_status', $paymentStatus))
            ->orderByDesc('id')
            ->paginate(10);

        return view('backend.'.$this->module.'.index', compact('pageData'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        $booking = Booking::query()
            ->with(['user', 'property', 'items.cabin', 'items.seat', 'payments'])
            ->findOrFail($id);

        return view('backend.'.$this->module.'.show', compact('booking'));
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            ActivityLogService::store(
                'bookings',
                'delete',
                (int) $booking->id,
                ['invoice_no' => $booking->invoice_no],
                'Booking deleted'
            );

            $booking->delete();

            return redirect()->route($this->module.'.index')->with('success', __('messages.deleted'));
        } catch (\Exception $e) {
            return redirect()->route($this->module.'.index')->with('error', __('messages.failed'));
        }
    }
}
