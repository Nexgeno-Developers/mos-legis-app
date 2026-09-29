<?php

return [

    'sms_provider' => env('SMS_PROVIDER', 'log'),

    'whatsapp_provider' => env('WHATSAPP_PROVIDER', 'log'),

    'email_provider' => env('EMAIL_PROVIDER', 'mail'),

    'providers' => [

        'sms' => [
            'smsgatewayhub' => [
                'api_key' => env('SMS_GATEWAY_HUB_API_KEY'),
                'sender_id' => env('SMS_GATEWAY_HUB_SENDER_ID'),
                'route' => env('SMS_GATEWAY_HUB_ROUTE', '1'),
            ],
            'twilio' => [
                'account_sid' => env('TWILIO_ACCOUNT_SID'),
                'auth_token' => env('TWILIO_AUTH_TOKEN'),
                'from' => env('TWILIO_FROM'),
            ],
        ],

        'whatsapp' => [
            'wati' => [
                'api_url' => env('WATI_API_URL'),
                'api_token' => env('WATI_API_TOKEN'),
            ],
        ],

        'email' => [
            'brevo' => [
                'api_key' => env('BREVO_API_KEY'),
                'from_email' => env('BREVO_FROM_EMAIL', env('MAIL_FROM_ADDRESS')),
                'from_name' => env('BREVO_FROM_NAME', env('MAIL_FROM_NAME')),
            ],
            'mail' => [
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'from_name' => env('MAIL_FROM_NAME'),
            ],
        ],

    ],

];
