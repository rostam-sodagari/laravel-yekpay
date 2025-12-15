<?php

return [
    'merchant_id' => env('YEKPAY_MERCHANT_ID'),

    // If true, use sandbox endpoints from docs.
    'sandbox' => env('YEKPAY_SANDBOX', false),

    'timeouts' => [
        'connect' => (float) env('YEKPAY_CONNECT_TIMEOUT', 5),
        'request' => (float) env('YEKPAY_TIMEOUT', 20),
    ],

    // Optional: allow overriding endpoints if YekPay changes them.
    'endpoints' => [
        'production' => [
            'request' => 'https://gate.ypsapi.com/api/payment/request',
            'start'   => 'https://gate.ypsapi.com/api/payment/start/{AUTHORITY}',
            'verify'  => 'https://gate.ypsapi.com/api/payment/verify',
        ],
        'sandbox' => [
            'request' => 'https://api.ypsapi.com/api/sandbox/request',
            'start'   => 'https://api.ypsapi.com/api/sandbox/payment/{AUTHORITY}',
            'verify'  => 'https://api.ypsapi.com/api/sandbox/verify',
        ],
    ],
];
