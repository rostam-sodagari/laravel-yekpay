# Laravel YekPay

A clean, production-ready Laravel package for integrating with the **YekPay payment gateway**.  
It wraps the full **request → redirect → verify** payment lifecycle using **typed DTOs**, **PHP enums**, and **localized messages**, with first-class Laravel support.

---

## Features

- Full YekPay payment flow (Request / Start / Verify)
- Strong typing via DTOs and PHP 8.1+ enums
- Currency codes as enum (no magic numbers)
- Localized gateway messages (lang files)
- Sandbox & production support
- Configurable endpoints and timeouts
- Testable architecture (Guzzle + Testbench)
- Laravel 10 / 11 compatible

---

## Requirements

- PHP 8.1+
- Laravel 10.x or 11.x

---

## Installation

Install via Composer:

composer require rostamsodagari/laravel-yekpay

Publish the configuration file:

php artisan vendor:publish --tag=yekpay-config

(Optional) Publish language files:

php artisan vendor:publish --tag=yekpay-lang

---

## Configuration

Set your credentials in `.env`:

YEKPAY_MERCHANT_ID=your-merchant-id  
YEKPAY_SANDBOX=true

Main config file: `config/yekpay.php`

return [
'merchant_id' => env('YEKPAY_MERCHANT_ID'),

    'sandbox' => env('YEKPAY_SANDBOX', false),

    'timeouts' => [
        'connect' => 5,
        'request' => 20,
    ],

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

---

## Payment Flow Overview

YekPay uses a three-step payment process:

1. Request Payment → Receive Authority
2. Redirect User → Gateway payment page
3. Verify Payment → Confirm transaction

This package mirrors that flow explicitly.

---

## Usage

### Request Payment

use RostamSodagari\YekPay\YekPay;  
use RostamSodagari\YekPay\DTO\RequestPaymentData;  
use RostamSodagari\YekPay\Enums\Currency;

$yekpay = app(YekPay::class);

$result = $yekpay->request(
new RequestPaymentData(
fromCurrency: Currency::IRR,
toCurrency: Currency::EUR,
email: 'john@example.com',
mobile: '+44123456789',
firstName: 'John',
lastName: 'Doe',
address: 'No.1, Second.St',
postalCode: 'SW1A 1AA',
country: 'UK',
city: 'London',
callback: route('yekpay.callback'),
orderNumber: 'ORD-1001',
amount: '1000000.00',
description: 'Order #1001',
)
);

if (! $result->ok() || ! $result->authority) {
abort(400, $result->message());
}

---

### Redirect User to Gateway

return redirect()->away(
$yekpay->startUrl($result->authority)
);

---

### Verify Payment (Callback)

use Illuminate\Http\Request;  
use RostamSodagari\YekPay\YekPay;

public function callback(Request $request, YekPay $yekpay)
{
$authority = (string) $request->input('Authority');

    if ($authority === '') {
        abort(400, 'Missing Authority');
    }

    $verify = $yekpay->verify($authority);

    if (! $verify->ok()) {
        abort(400, $verify->message());
    }

    return response()->json([
        'status'    => 'paid',
        'reference' => $verify->reference,
        'order'     => $verify->orderNo,
        'amount'    => $verify->amount,
    ]);
}

---

## Currency Enum

All supported currencies are defined as a PHP enum:

use RostamSodagari\YekPay\Enums\Currency;

Currency::EUR->value; // 978  
Currency::IRR->value; // 364

Note on TRY currency:  
YekPay documentation contains conflicting TRY codes. This package uses the official appendix value by default, but you should confirm via sandbox before production use.

---

## Localization (Lang Files)

Messages are resolved from Laravel translation files:

$result->message();

Default languages:
- English
- Persian (Farsi)

Override or customize by publishing:

php artisan vendor:publish --tag=yekpay-lang

Then edit:

lang/vendor/yekpay/en/yekpay.php  
lang/vendor/yekpay/fa/yekpay.php

Fallback behavior:
- Known code → translated message
- Unknown code → "Unknown gateway response code: X"

---

## Error Handling Philosophy

- Gateway responses are never trusted blindly
- ok() checks numeric result codes
- message() returns localized, user-safe messages
- Raw gateway payload is preserved internally for logging

---

## Testing

This package is fully testable and ships with:

- Orchestra Testbench
- Guzzle MockHandler
- No real HTTP calls

Run tests:

vendor/bin/phpunit  
or  
composer test

---

## Security Best Practices

- Always verify payment before marking orders as paid
- Never trust callback parameters alone
- Persist gateway Reference for audit trails
- Enable sandbox during development

---

## Roadmap

- Facade support
- Webhook signature validation
- Retry & idempotency helpers
- Laravel Cashier-style fluent API

---

## License

MIT © Rostam Sodagari
