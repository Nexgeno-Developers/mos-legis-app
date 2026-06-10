<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        NotificationTemplate::updateOrCreate(
            ['slug' => 'booking_created'],
            [
                'name' => 'Booking Created',
                'sms_template' => 'Booking #{booking_no} confirmed.',
                'whatsapp_template' => "Hello {customer_name}\n\nBooking #{booking_no} confirmed.\n\nAmount: {amount}",
                'email_subject' => 'Booking Confirmed',
                'email_template' => '<h2>Hello {customer_name}</h2><p>Booking #{booking_no} confirmed.</p><p>Amount: {amount}</p>',
                'sms_enabled' => true,
                'whatsapp_enabled' => true,
                'email_enabled' => true,
                'status' => true,
            ]
        );
    }
}
