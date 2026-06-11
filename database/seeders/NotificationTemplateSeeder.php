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
                'whatsapp_options' => [],
                'email_subject' => 'Booking Confirmed',
                'email_template' => '<h2>Hello {customer_name}</h2><p>Booking #{booking_no} confirmed.</p><p>Amount: {amount}</p>',
                'sms_enabled' => true,
                'whatsapp_enabled' => true,
                'email_enabled' => true,
                'status' => true,
            ]
        );

        NotificationTemplate::updateOrCreate(
            ['slug' => 'booking_status_changed'],
            [
                'name' => 'Booking Status Changed',
                'sms_template' => 'Booking #{booking_no} status updated to {booking_status}.',
                'whatsapp_template' => "birthday",
                'whatsapp_options' => [
                    'wati' => [
                        'parameters' => [
                            [
                                'name' => 'name',
                                'value' => '{customer_name}',
                            ]
                        ],
                    ],
                ],
                'email_subject' => 'Booking Status Updated',
                'email_template' => '<h2>Hello {customer_name}</h2><p>Booking <strong>#{booking_no}</strong> status has been updated.</p><p><strong>Previous:</strong> {previous_status}<br><strong>Current:</strong> {booking_status}</p><p>Amount: {amount}</p>',
                'sms_enabled' => true,
                'whatsapp_enabled' => true,
                'email_enabled' => true,
                'status' => true,
            ]
        );
    }
}
