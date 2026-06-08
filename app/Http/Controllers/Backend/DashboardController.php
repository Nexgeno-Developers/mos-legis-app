<?php

namespace App\Http\Controllers\Backend;

use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Role;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:dashboard view')->only(['index']);
    }

    public function index()
    {
        $customerRoleId = (int) Role::where('name', Role::CUSTOMER)->value('id');

        $stats = [
            'bookings' => [
                'total' => Booking::count(),
                'active' => Booking::where('booking_status', 'active')->count(),
                'reserved' => Booking::where('booking_status', 'reserved')->count(),
                'completed' => Booking::where('booking_status', 'completed')->count(),
                'cancelled' => Booking::where('booking_status', 'cancelled')->count(),
            ],
            'payments' => [
                'paid' => (float) Payment::where('payment_status', 'paid')->sum('amount'),
                'pending' => (float) Payment::where('payment_status', 'pending')->sum('amount'),
            ],
            'customers' => [
                'total' => $customerRoleId ? User::where('role_id', $customerRoleId)->count() : 0,
                'active' => $customerRoleId ? User::where('role_id', $customerRoleId)->where('is_active', true)->count() : 0,
                'inactive' => $customerRoleId ? User::where('role_id', $customerRoleId)->where('is_active', false)->count() : 0,
            ],
            'properties' => [
                'total' => Property::count(),
                'cabins' => Cabin::count(),
                'seats' => Seat::count(),
            ],
        ];

        return view('backend.dashboard', compact('stats'));
    }
}
