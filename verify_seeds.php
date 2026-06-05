<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookings = \App\Models\Booking::count();
$items = \App\Models\BookingItem::count();
$payments = \App\Models\Payment::count();

echo "Bookings: $bookings\n";
echo "Booking Items: $items\n";
echo "Payments: $payments\n\n";

echo "=== Booking Statuses ===\n";
foreach (\App\Models\Booking::select('booking_status')->distinct()->get() as $b) {
    echo "  - ".$b->booking_status."\n";
}

echo "\n=== Payment Statuses ===\n";
foreach (\App\Models\Payment::select('payment_status')->distinct()->get() as $p) {
    echo "  - ".$p->payment_status."\n";
}

echo "\n=== Payment Methods ===\n";
foreach (\App\Models\Payment::select('payment_method')->distinct()->get() as $p) {
    echo "  - ".$p->payment_method."\n";
}

echo "\n=== Booking Invoices + Statuses ===\n";
foreach (\App\Models\Booking::select('invoice_no','booking_status','payment_status','grand_total_amount')->orderBy('id')->get() as $b) {
    echo $b->invoice_no." | ".$b->booking_status." | ".$b->payment_status." | ".$b->grand_total_amount."\n";
}

echo "\n=== Payments per Booking ===\n";
foreach (\App\Models\Booking::with('payments')->orderBy('id')->get() as $b) {
    $paid = $b->payments->where('payment_status','paid')->sum('amount');
    echo $b->invoice_no." | total: ".$b->grand_total_amount." | paid_via_payments: ".$paid."\n";
}
