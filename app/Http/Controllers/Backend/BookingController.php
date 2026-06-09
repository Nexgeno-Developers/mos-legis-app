<?php

namespace App\Http\Controllers\Backend;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Payment;
use App\Models\Property;
use App\Services\ActivityLogService;
use App\Services\BookingPaymentService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;

class BookingController extends BaseController
{
    protected $module;

    public function __construct()
    {
        $this->module = 'bookings';
        view()->share('module', $this->module);

        $this->middleware('permission:bookings view')->only(['index', 'show', 'showInvoice', 'seatAvailability']);
        $this->middleware('permission:bookings create')->only(['create', 'store']);
        $this->middleware('permission:bookings edit')->only(['edit', 'update', 'storePayment', 'destroyPayment']);
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

        $bookingStatuses = Booking::BOOKING_STATUSES;
        $paymentStatuses = Booking::PAYMENT_STATUSES;

        return view('backend.'.$this->module.'.index', compact('pageData', 'bookingStatuses', 'paymentStatuses'));
    }

    public function seatAvailability()
    {
        $properties = Property::query()
            ->with([
                'cabins' => fn ($query) => $query->orderBy('name'),
                'cabins.seats' => fn ($query) => $query->orderBy('seat_no'),
            ])
            ->orderBy('name')
            ->get();

        $seatBookings = BookingItem::query()
            ->whereNotNull('seat_id')
            ->whereHas('booking', fn ($query) => $query->whereIn('booking_status', ['active', 'reserved']))
            ->with(['booking.user'])
            ->get();

        $seatStatusMap = [];

        foreach ($seatBookings as $item) {
            $booking = $item->booking;
            $seatId = $item->seat_id;
            $status = $booking->booking_status;

            if (
                ! isset($seatStatusMap[$seatId])
                || ($seatStatusMap[$seatId]['status'] === 'reserved' && $status === 'active')
            ) {
                $seatStatusMap[$seatId] = [
                    'status' => $status,
                    'user_name' => $booking->user?->name,
                    'start_datetime' => $booking->start_datetime,
                    'end_datetime' => $booking->end_datetime,
                ];
            }
        }

        return view('backend.'.$this->module.'.seat-availability', compact('properties', 'seatStatusMap'));
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

        $manualPaymentMethods = Payment::MANUAL_PAYMENT_METHODS;

        return view('backend.'.$this->module.'.show', compact('booking', 'manualPaymentMethods'));
    }

    public function showInvoice(int $id, InvoiceService $invoiceService)
    {
        $invoice = $invoiceService->getInvoiceData($id);

        return view('invoice.index', compact('invoice'));
    }

    public function edit($id)
    {
        $booking = Booking::findOrFail($id);

        $bookingStatuses = Booking::BOOKING_STATUSES;

        return view('backend.'.$this->module.'.edit', compact('booking', 'bookingStatuses'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['booking_status' => $request->booking_status]);

        ActivityLogService::store(
            'bookings',
            'update',
            (int) $booking->id,
            ['booking_status' => $request->booking_status],
            'Booking status updated'
        );

        return response()->json(['status' => true, 'notification' => __('messages.updated')]);
    }

    public function storePayment(Request $request, string $booking)
    {
        $bookingModel = Booking::findOrFail($booking);

        $rules = [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => ['required', Rule::in(Payment::MANUAL_PAYMENT_METHODS)],
            'paid_at' => 'required|date',
            'remarks' => 'nullable|string|max:1000',
        ];

        $methodRules = match ($request->input('payment_method')) {
            'cash' => [
                'receipt_number' => 'required|string|max:100',
                'received_by' => 'required|string|max:100',
            ],
            'cheque' => [
                'cheque_number' => 'required|string|max:100',
                'bank_name' => 'required|string|max:100',
                'cheque_date' => 'required|date',
            ],
            'bank_transfer' => [
                'utr_no' => 'required|string|max:100',
                'bank_name' => 'required|string|max:100',
            ],
            default => [],
        };

        $validated = $request->validate(array_merge($rules, $methodRules));

        if (! BookingPaymentService::canAcceptPayment($bookingModel, (float) $validated['amount'])) {
            return response()->json([
                'status' => false,
                'notification' => __('messages.payment_exceeds_balance'),
            ]);
        }

        try {
            $payment = BookingPaymentService::createManualPayment($bookingModel, $validated);

            ActivityLogService::store(
                'bookings',
                'payment_create',
                (int) $bookingModel->id,
                [
                    'payment_id' => $payment->payment_id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                ],
                'Manual payment added'
            );

            return response()->json(['status' => true, 'notification' => __('messages.created')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    public function destroyPayment(string $booking, string $payment)
    {
        try {
            $bookingModel = Booking::findOrFail($booking);
            $paymentModel = Payment::query()
                ->where('payable_type', 'booking')
                ->where('payable_id', $bookingModel->id)
                ->findOrFail($payment);

            ActivityLogService::store(
                'bookings',
                'payment_delete',
                (int) $bookingModel->id,
                [
                    'payment_id' => $paymentModel->payment_id,
                    'amount' => $paymentModel->amount,
                    'payment_method' => $paymentModel->payment_method,
                ],
                'Manual payment deleted'
            );

            BookingPaymentService::deleteManualPayment($bookingModel, $paymentModel);

            return response()->json(['status' => true, 'notification' => __('messages.deleted')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
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

            return response()->json(['status' => true, 'notification' => __('messages.deleted')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }
}
