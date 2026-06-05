<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Cabin;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Role;
use App\Models\Seat;
use App\Models\SeatPricing;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CoworkingDataSeeder extends Seeder
{
    private int $invoiceCounter;

    public function __construct()
    {
        $this->invoiceCounter = (int) (Booking::max('invoice_no') ?? 'INV-1000');
        $this->invoiceCounter = (int) str_replace('INV-', '', $this->invoiceCounter);
    }

    private array $fakerNames = [
        'Arjun Mehta', 'Priya Sharma', 'Rahul Verma', 'Anita Desai', 'Vikram Singh',
        'Neha Kapoor', 'Rohan Gupta', 'Sneha Patel', 'Karan Joshi', 'Meera Reddy',
        'Aditya Iyer', 'Kavita Nair', 'Siddharth Rao', 'Pooja Malhotra', 'Tarun Bhatia',
        'Ritu Choudhury', 'Manish Kulkarni', 'Deepika Saxena', 'Amit Dhillon', 'Swati Pillai',
    ];

    private array $fakerPhones = [
        '+91-9876543210', '+91-9123456789', '+91-9988776655', '+91-9765432109',
        '+91-9654321098', '+91-9543210987', '+91-9432109876', '+91-9321098765',
        '+91-9210987654', '+91-9109876543',
    ];

    public function run(): void
    {
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', Role::CUSTOMER)->first();

        if (! $staffRole || ! $customerRole) {
            $this->command?->warn('Staff or customer role not found. Run RoleSeeder first.');

            return;
        }

        $this->seedStaffUsers($staffRole);
        $this->seedCustomers($customerRole);
        $this->seedPropertyWithCabinsAndSeats();
        $this->seedBookings();
    }

    private function seedStaffUsers(Role $staffRole): void
    {
        $staffUsers = [
            ['name' => 'Staff User One', 'email' => 'staff1@example.com'],
            ['name' => 'Staff User Two', 'email' => 'staff2@example.com'],
        ];

        foreach ($staffUsers as $staff) {
            $user = User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'role_id' => $staffRole->id,
                    'name' => $staff['name'],
                    'phone' => null,
                    'password' => Hash::make($staff['email']),
                    'is_active' => 1,
                ]
            );

            $user->syncRoles([$staffRole->name]);
        }
    }

    private function seedCustomers(Role $customerRole): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $email = "customer{$i}@example.com";

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'role_id' => $customerRole->id,
                    'name' => "Customer {$i}",
                    'phone' => '9'.str_pad((string) $i, 9, '0', STR_PAD_LEFT),
                    'password' => Hash::make(Str::random(32)),
                    'is_active' => 1,
                ]
            );

            $user->syncRoles([$customerRole->name]);

            UserDetail::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'detail_key' => 'company_name',
                ],
                [
                    'detail_value' => "Customer Company {$i}",
                ]
            );
        }
    }

    private function seedPropertyWithCabinsAndSeats(): void
    {
        $property = Property::firstOrCreate(
            ['email' => 'property@ctrlshift.co.in'],
            [
                'name' => 'CtrlShift Main Hub',
                'phone' => '+919004936200',
                'address' => 'Unit 201-b, Kohinoor City Mall, Kurla West, Mumbai 400070.',
                'facilities' => 'Wi-Fi, Meeting rooms, Pantry, Parking, 24/7 access',
                'thumbnail' => '0',
                'images' => '0',
                'status' => 1,
            ]
        );

        $cabins = [
            ['name' => 'Zone A', 'type' => 'flexible'],
            ['name' => 'Zone B', 'type' => 'flexible'],
            ['name' => 'Zone C', 'type' => 'flexible'],
        ];

        foreach ($cabins as $cabinData) {
            $cabin = Cabin::firstOrCreate(
                [
                    'property_id' => $property->id,
                    'name' => $cabinData['name'],
                ],
                [
                    'type' => $cabinData['type'],
                    'thumbnail' => '0',
                    'images' => '0',
                    'status' => 1,
                ]
            );

            if ($cabin->seats()->exists()) {
                continue;
            }

            $seatCount = random_int(1, 10);

            for ($seatIndex = 1; $seatIndex <= $seatCount; $seatIndex++) {
                $seat = Seat::create([
                    'property_id' => $property->id,
                    'cabin_id' => $cabin->id,
                    'seat_no' => strtoupper(substr($cabinData['type'], 0, 1)).$cabin->id.'-'.$seatIndex,
                    'status' => 1,
                ]);

                foreach (['monthly' => 5000 + ($seatIndex * 250), 'yearly' => 50000 + ($seatIndex * 2500)] as $duration => $price) {
                    SeatPricing::updateOrCreate(
                        [
                            'seat_id' => $seat->id,
                            'duration' => $duration,
                        ],
                        [
                            'price' => $price,
                        ]
                    );
                }
            }
        }
    }

    private function seedBookings(): void
    {
        $customers = User::whereHas('role', function ($query) {
            $query->where('name', Role::CUSTOMER);
        })->get();

        $properties = Property::where('status', 1)->get();
        $cabins = Cabin::where('status', 1)
            ->with(['seats' => function ($query) {
                $query->where('status', 1)->with('pricing');
            }])
            ->get();

        if ($customers->isEmpty() || $properties->isEmpty() || $cabins->isEmpty()) {
            $this->command?->warn('Insufficient base data for bookings. Run prerequisite seeders first.');

            return;
        }

        $scenarios = [
            fn () => $this->scenario1($customers, $properties, $cabins),
            fn () => $this->scenario2($customers, $properties, $cabins),
            fn () => $this->scenario3($customers, $properties, $cabins),
            fn () => $this->scenario4($customers, $properties, $cabins),
            fn () => $this->scenario5($customers, $properties, $cabins),
            fn () => $this->scenario6($customers, $properties, $cabins),
            fn () => $this->scenario7($customers, $properties, $cabins),
            fn () => $this->scenario8($customers, $properties, $cabins),
            fn () => $this->scenario9($customers, $properties, $cabins),
            fn () => $this->scenario10($customers, $properties, $cabins),
        ];

        foreach ($scenarios as $scenario) {
            DB::transaction(fn () => $scenario());
        }
    }

    private function getRandomCustomer($customers)
    {
        return $customers[random_int(0, $customers->count() - 1)];
    }

    private function getRandomProperty($properties)
    {
        return $properties[random_int(0, $properties->count() - 1)];
    }

    private function getRandomCabin($cabins)
    {
        $availableCabins = $cabins->filter(fn ($cabin) => $cabin->seats->where('status', 1)->count() > 0);

        return $availableCabins->isNotEmpty() ? $availableCabins[random_int(0, $availableCabins->count() - 1)] : null;
    }

    private function getRandomSeat($cabins)
    {
        $allSeats = collect();
        foreach ($cabins as $cabin) {
            $allSeats = $allSeats->merge($cabin->seats);
        }

        $availableSeats = $allSeats->where('status', 1);

        return $availableSeats->isNotEmpty() ? $availableSeats[random_int(0, $availableSeats->count() - 1)] : null;
    }

    private function getSeatsFromCabin($cabin): \Illuminate\Support\Collection
    {
        return $cabin->seats->where('status', 1);
    }

    private function getSeatPrice($seat, string $durationType): ?float
    {
        $pricing = $seat->pricing->firstWhere('duration', $durationType);

        return $pricing ? (float) $pricing->price : null;
    }

    private function generateInvoiceNo(): string
    {
        $this->invoiceCounter++;

        return 'INV-'.$this->invoiceCounter;
    }

    private function randomDate(string $startRange, string $endRange): string
    {
        $start = strtotime($startRange);
        $end = strtotime($endRange);
        $timestamp = random_int($start, $end);

        return date('Y-m-d', $timestamp);
    }

    private function randomDatetime(string $date): string
    {
        return $date.' '.sprintf('%02d:%02d:00', random_int(0, 23), random_int(0, 59));
    }

    private function randomFromArray(array $array)
    {
        return $array[array_rand($array)];
    }

    private function fakeName(): string
    {
        return $this->randomFromArray($this->fakerNames);
    }

    private function fakePhone(): string
    {
        return $this->randomFromArray($this->fakerPhones);
    }

    private function calculateMonthsBetween(string $startDate, string $endDate): int
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        return (int) $start->diff($end)->m + ($start->diff($end)->y * 12) + 1;
    }

    private function createBooking($customer, $property, ?string $startDate, ?string $endDate, ?string $durationType, ?string $bookingStatus, ?float $subtotal, ?float $taxRate, ?float $taxAmount, ?float $discount, ?float $grandTotal, ?string $paymentStatus): Booking
    {
        $startDate = $startDate ?? $this->randomDate('-6 months', 'today');
        $endDate = $endDate ?? $this->randomDate($startDate, '+1 year');
        $durationType = $durationType ?? $this->randomFromArray(['monthly', 'yearly']);
        $bookingStatus = $bookingStatus ?? $this->randomFromArray(['reserved', 'active', 'completed', 'cancelled']);
        $taxRate = $taxRate ?? random_int(0, 18);
        $discount = $discount ?? (float) random_int(0, 500);
        $paymentStatus = $paymentStatus ?? $this->randomFromArray(['paid', 'unpaid', 'partially_paid']);

        $subtotal = $subtotal ?? 0;
        $taxAmount = $taxAmount ?? round($subtotal * $taxRate / 100, 2);
        $grandTotal = $grandTotal ?? max(0, $subtotal + $taxAmount - $discount);

        /** @var Booking $booking */
        $booking = Booking::create([
            'invoice_no' => $this->generateInvoiceNo(),
            'user_id' => $customer->id,
            'start_datetime' => $this->randomDatetime($startDate),
            'end_datetime' => $this->randomDatetime($endDate),
            'subtotal_amount' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total_amount' => $grandTotal,
            'duration_type' => $durationType,
            'booking_status' => $bookingStatus,
            'payment_status' => $paymentStatus,
        ]);

        return $booking;
    }

    private function createBookingItems(Booking $booking, $property, array $itemDefinitions): void
    {
        $usedSeats = [];

        foreach ($itemDefinitions as $item) {
            $seatKey = ($item['cabin_id'] ?? null).'_'.($item['seat_id'] ?? null);

            if (in_array($seatKey, $usedSeats, true)) {
                continue;
            }

            $usedSeats[] = $seatKey;
            $unitPrice = (float) $item['unit_price'];
            $quantity = $item['quantity'];
            $totalPrice = round($unitPrice * $quantity, 2);

            BookingItem::create([
                'booking_id' => $booking->id,
                'property_id' => $property->id,
                'cabin_id' => $item['cabin_id'] ?? null,
                'seat_id' => $item['seat_id'] ?? null,
                'occupant_name' => $this->fakeName(),
                'occupant_phone' => $this->fakePhone(),
                'occupant_id_proof_no' => 'ID-'.random_int(10000, 99999),
                'amount' => $totalPrice,
                'kyc_status' => $this->randomFromArray(['pending', 'verified']),
            ]);
        }
    }

    private function createPayments(Booking $booking, $customer, float $totalAmount, array $paymentDefinitions): void
    {
        $paymentTransactions = [];

        foreach ($paymentDefinitions as $payment) {
            $amount = (float) $payment['amount'];
            $status = $payment['status'];

            $paidAt = in_array($status, ['paid']) ? $this->randomDate(
                $booking->start_datetime->format('Y-m-d'),
                $booking->start_datetime->format('Y-m-d').' +30 days'
            ) : null;

            $paymentId = 'PAY-'.strtoupper(Str::random(16));
            $txnRef = 'TXN-'.strtoupper(Str::random(12));

            Payment::create([
                'user_id' => $customer->id,
                'payable_type' => 'booking',
                'payable_id' => $booking->id,
                'amount' => $amount,
                'payment_method' => $payment['method'],
                'payment_status' => $status,
                'payment_details' => [
                    'transaction_ref' => $txnRef,
                    'paid_via' => $payment['method'],
                ],
                'payment_id' => $paymentId,
                'remarks' => $payment['remarks'] ?? null,
                'paid_at' => $paidAt,
            ]);

            $paymentTransactions[] = ['amount' => $amount, 'status' => $status];
        }

        $totalPaid = collect($paymentTransactions)
            ->filter(fn ($p) => $p['status'] === 'paid')
            ->sum('amount');

        if ($totalPaid > 0) {
            $booking->update([
                'payment_status' => $totalPaid >= $totalAmount ? 'paid' : 'partially_paid',
            ]);
        }
    }

    private function scenario1($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);
        $cabin = $this->getRandomCabin($cabins);
        $seat = $this->getRandomSeat($cabins);

        $durationType = $this->randomFromArray(['monthly', 'yearly']);
        $startDate = $this->randomDate('-6 months', '-1 month');
        $endDate = $this->randomDate($startDate, '+12 months');
        $unitPrice = $seat && $seat->pricing->isNotEmpty() ? $this->getSeatPrice($seat, $durationType) : 5000;

        $months = $this->calculateMonthsBetween($startDate, $endDate);
        $subtotal = round($unitPrice * $months, 2);
        $taxRate = 18;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $grandTotal = $subtotal + $taxAmount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'completed',
            $subtotal, $taxRate, $taxAmount, 0, $grandTotal, 'paid'
        );

        $this->createBookingItems($booking, $property, [
            ['cabin_id' => $cabin->id, 'seat_id' => $seat ? $seat->id : null, 'quantity' => 1, 'unit_price' => $unitPrice],
        ]);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $grandTotal, 'method' => 'online', 'status' => 'paid', 'remarks' => 'Full payment via online transfer'],
        ]);
    }

    private function scenario2($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);
        $cabin = $this->getRandomCabin($cabins);
        $seats = $this->getSeatsFromCabin($cabin)->take(random_int(2, min(5, $cabin->seats->count())))->values();

        if ($seats->isEmpty()) {
            $seat = $this->getRandomSeat($cabins);
            $seats = $seat ? collect([$seat]) : collect();
        }

        $durationType = 'monthly';
        $startDate = '-3 months';
        $endDate = '+3 months';
        $unitPrice = 6000;

        $subtotal = round($unitPrice * $seats->count() * 6, 2);
        $taxRate = 12;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $discount = 500;
        $grandTotal = $subtotal + $taxAmount - $discount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'completed',
            $subtotal, $taxRate, $taxAmount, $discount, $grandTotal, 'paid'
        );

        $itemDefinitions = [];
        foreach ($seats as $seat) {
            $itemDefinitions[] = ['cabin_id' => $cabin->id, 'seat_id' => $seat->id, 'quantity' => 1, 'unit_price' => $unitPrice];
        }

        $this->createBookingItems($booking, $property, $itemDefinitions);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $grandTotal, 'method' => 'online', 'status' => 'paid', 'remarks' => 'Full payment via bank transfer'],
        ]);
    }

    private function scenario3($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);

        $selectedCabins = $cabins->take(random_int(2, 3))->values();

        $durationType = 'yearly';
        $startDate = $this->randomPastDate();
        $endDate = $this->randomDate($startDate, '+18 months');
        $unitPrice = 8000;

        $subtotal = round($unitPrice * 12 * $selectedCabins->count(), 2);
        $taxRate = 18;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $grandTotal = $subtotal + $taxAmount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'active',
            $subtotal, $taxRate, $taxAmount, 0, $grandTotal, 'paid'
        );

        $itemDefinitions = [];
        foreach ($selectedCabins as $cabin) {
            $seat = $this->getSeatsFromCabin($cabin)->first();
            $itemDefinitions[] = ['cabin_id' => $cabin->id, 'seat_id' => $seat ? $seat->id : null, 'quantity' => 1, 'unit_price' => $unitPrice];
        }

        $this->createBookingItems($booking, $property, $itemDefinitions);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $grandTotal, 'method' => 'online', 'status' => 'paid', 'remarks' => 'Full payment via online for multiple cabins'],
        ]);
    }

    private function scenario4($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);

        $selectedCabins = $cabins->take(random_int(2, 3))->values();

        $durationType = 'monthly';
        $startDate = '-2 months';
        $endDate = '+4 months';
        $unitPrice = 7000;
        $months = 6;

        $subtotal = 0;
        $itemDefinitions = [];
        foreach ($selectedCabins as $cabin) {
            $seats = $this->getSeatsFromCabin($cabin)->take(random_int(2, 3))->values();
            foreach ($seats as $seat) {
                $subtotal += $unitPrice * $months;
                $itemDefinitions[] = ['cabin_id' => $cabin->id, 'seat_id' => $seat->id, 'quantity' => 1, 'unit_price' => $unitPrice];
            }
        }
        $subtotal = round($subtotal, 2);

        $taxRate = 12;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $discount = 1000;
        $grandTotal = $subtotal + $taxAmount - $discount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'completed',
            $subtotal, $taxRate, $taxAmount, $discount, $grandTotal, 'paid'
        );

        $this->createBookingItems($booking, $property, $itemDefinitions);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $grandTotal, 'method' => 'cash', 'status' => 'paid', 'remarks' => 'Full payment via cash'],
        ]);
    }

    private function scenario5($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);

        $selectedCabins = $cabins->take(random_int(2, 3))->values();

        $durationType = 'yearly';
        $startDate = $this->randomPastDate();
        $endDate = $this->randomDate($startDate, '+18 months');
        $unitPrice = 9000;

        $subtotal = round($unitPrice * 12 * $selectedCabins->count(), 2);
        $taxRate = 18;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $discount = 2000;
        $grandTotal = $subtotal + $taxAmount - $discount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'active',
            $subtotal, $taxRate, $taxAmount, $discount, $grandTotal, 'partially_paid'
        );

        $itemDefinitions = [];
        foreach ($selectedCabins as $cabin) {
            $seats = $this->getSeatsFromCabin($cabin)->take(random_int(1, 3))->values();
            foreach ($seats as $seat) {
                $itemDefinitions[] = ['cabin_id' => $cabin->id, 'seat_id' => $seat->id, 'quantity' => 1, 'unit_price' => $unitPrice];
            }
        }

        $this->createBookingItems($booking, $property, $itemDefinitions);

        $partialAmount = round($grandTotal * 0.5, 2);
        $balanceAmount = round($grandTotal - $partialAmount, 2);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $partialAmount, 'method' => 'online', 'status' => 'paid', 'remarks' => 'Initial advance payment'],
            ['amount' => $balanceAmount, 'method' => 'online', 'status' => 'pending', 'remarks' => 'Remaining balance pending'],
        ]);
    }

    private function scenario6($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);
        $cabin = $this->getRandomCabin($cabins);

        $durationType = 'monthly';
        $startDate = $this->randomPastDate();
        $endDate = $this->randomDate($startDate, '+6 months');
        $unitPrice = 35000;
        $months = 6;

        $subtotal = round($unitPrice * $months, 2);
        $taxRate = 18;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $discount = 3000;
        $grandTotal = $subtotal + $taxAmount - $discount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'completed',
            $subtotal, $taxRate, $taxAmount, $discount, $grandTotal, 'paid'
        );

        $this->createBookingItems($booking, $property, [
            ['cabin_id' => $cabin->id, 'seat_id' => null, 'quantity' => 1, 'unit_price' => $unitPrice],
        ]);

        $payment1 = round($grandTotal * 0.6, 2);
        $payment2 = round($grandTotal - $payment1, 2);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $payment1, 'method' => 'cash', 'status' => 'paid', 'remarks' => 'First installment'],
            ['amount' => $payment2, 'method' => 'cash', 'status' => 'paid', 'remarks' => 'Second installment'],
        ]);
    }

    private function scenario7($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);
        $seat = $this->getRandomSeat($cabins);

        $durationType = 'monthly';
        $startDate = 'today';
        $endDate = '+1 month';
        $unitPrice = $seat && $seat->pricing->isNotEmpty() ? $this->getSeatPrice($seat, $durationType) : 5000;

        $subtotal = round($unitPrice * 1, 2);
        $taxRate = 12;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $grandTotal = $subtotal + $taxAmount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'reserved',
            $subtotal, $taxRate, $taxAmount, 0, $grandTotal, 'unpaid'
        );

        $this->createBookingItems($booking, $property, [
            ['cabin_id' => $seat ? $seat->cabin_id : null, 'seat_id' => $seat ? $seat->id : null, 'quantity' => 1, 'unit_price' => $unitPrice],
        ]);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => 0, 'method' => 'online', 'status' => 'pending', 'remarks' => 'Payment pending - awaiting customer confirmation'],
        ]);
    }

    private function scenario8($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);
        $seat = $this->getRandomSeat($cabins);

        $durationType = 'monthly';
        $startDate = '-1 month';
        $endDate = 'today';
        $unitPrice = $seat && $seat->pricing->isNotEmpty() ? $this->getSeatPrice($seat, $durationType) : 5000;

        $subtotal = round($unitPrice * 1, 2);
        $taxRate = 12;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $discount = 200;
        $grandTotal = $subtotal + $taxAmount - $discount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'completed',
            $subtotal, $taxRate, $taxAmount, $discount, $grandTotal, 'partially_paid'
        );

        $partialPayment = round($grandTotal * 0.4, 2);

        $this->createBookingItems($booking, $property, [
            ['cabin_id' => $seat ? $seat->cabin_id : null, 'seat_id' => $seat ? $seat->id : null, 'quantity' => 1, 'unit_price' => $unitPrice],
        ]);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $partialPayment, 'method' => 'cash', 'status' => 'paid', 'remarks' => 'Partial payment received'],
        ]);
    }

    private function scenario9($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);
        $cabin = $this->getRandomCabin($cabins);
        $seatA = $this->getSeatsFromCabin($cabin)->first();
        $seatB = $this->getRandomSeat($cabins);

        $durationType = 'yearly';
        $startDate = $this->randomPastDate();
        $endDate = $this->randomDate($startDate, '+1 year');
        $unitPrice = 8500;
        $annualPrice = $unitPrice * 12;

        $seatAPrice = $seatA && $seatA->pricing->isNotEmpty() ? $this->getSeatPrice($seatA, $durationType) : $annualPrice;
        $seatBPrice = $seatB && $seatB->pricing->isNotEmpty() ? $this->getSeatPrice($seatB, $durationType) : $annualPrice;
        $cabinPrice = 35000;

        $subtotal = round($cabinPrice + $seatAPrice + $seatBPrice, 2);
        $taxRate = 18;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $discount = 1500;
        $grandTotal = $subtotal + $taxAmount - $discount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'active',
            $subtotal, $taxRate, $taxAmount, $discount, $grandTotal, 'partially_paid'
        );

        $this->createBookingItems($booking, $property, [
            ['cabin_id' => $cabin->id, 'seat_id' => null, 'quantity' => 1, 'unit_price' => $cabinPrice],
            ['cabin_id' => $seatA ? $seatA->cabin_id : null, 'seat_id' => $seatA ? $seatA->id : null, 'quantity' => 1, 'unit_price' => $seatAPrice],
            ['cabin_id' => $seatB ? $seatB->cabin_id : null, 'seat_id' => $seatB ? $seatB->id : null, 'quantity' => 1, 'unit_price' => $seatBPrice],
        ]);

        $payment1 = round($grandTotal * 0.5, 2);
        $payment2 = round($grandTotal - $payment1, 2);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $payment1, 'method' => 'online', 'status' => 'paid', 'remarks' => 'First installment via online'],
            ['amount' => $payment2, 'method' => 'cheque', 'status' => 'processing', 'remarks' => 'Second installment via cheque - processing'],
        ]);
    }

    private function scenario10($customers, $properties, $cabins): void
    {
        $customer = $this->getRandomCustomer($customers);
        $property = $this->getRandomProperty($properties);

        $selectedCabins = $cabins->take(random_int(3, 5))->values();

        $durationType = 'yearly';
        $startDate = $this->randomPastDate();
        $endDate = $this->randomDate($startDate, '+2 years');
        $unitPrice = 7500;

        $itemDefinitions = [];
        $subtotal = 0;
        foreach ($selectedCabins as $cabin) {
            $subtotal += $unitPrice * 12;
            $itemDefinitions[] = ['cabin_id' => $cabin->id, 'seat_id' => null, 'quantity' => 1, 'unit_price' => $unitPrice * 12];

            $seats = $this->getSeatsFromCabin($cabin)->take(random_int(1, 2))->values();
            foreach ($seats as $seat) {
                $seatPrice = $seat->pricing->isNotEmpty() ? $this->getSeatPrice($seat, $durationType) : $unitPrice * 12;
                $subtotal += $seatPrice;
                $itemDefinitions[] = ['cabin_id' => $cabin->id, 'seat_id' => $seat->id, 'quantity' => 1, 'unit_price' => $seatPrice];
            }
        }
        $subtotal = round($subtotal, 2);

        $taxRate = 18;
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $discount = 5000;
        $grandTotal = $subtotal + $taxAmount - $discount;

        $booking = $this->createBooking(
            $customer, $property, $startDate, $endDate, $durationType, 'active',
            $subtotal, $taxRate, $taxAmount, $discount, $grandTotal, 'partially_paid'
        );

        $this->createBookingItems($booking, $property, $itemDefinitions);

        $payment1 = round($grandTotal * 0.35, 2);
        $payment2 = round($grandTotal * 0.35, 2);
        $payment3 = round($grandTotal - $payment1 - $payment2, 2);

        $this->createPayments($booking, $customer, $grandTotal, [
            ['amount' => $payment1, 'method' => 'online', 'status' => 'paid', 'remarks' => 'First installment - online'],
            ['amount' => $payment2, 'method' => 'online', 'status' => 'paid', 'remarks' => 'Second installment - bank transfer'],
            ['amount' => $payment3, 'method' => 'cheque', 'status' => 'processing', 'remarks' => 'Final installment - cheque clearance pending'],
        ]);
    }

    private function randomPastDate(): string
    {
        return $this->randomDate('-6 months', '-1 month');
    }
}
