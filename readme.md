# Laravel YekPay

A clean, production-ready Laravel package for integrating with the **YekPay payment gateway**.  
It wraps the full **request → redirect → verify** payment lifecycle using **typed DTOs** and **PHP enums**, with facade Laravel support.


[Read Official Document](https://docs.yekpay.com)
---

## Features

- Full YekPay payment flow (Request / Start / Verify)
- Strong typing via DTOs and PHP 8.1+ enums
- Currency codes as enum (no magic numbers)
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

---

## Configuration

Set your credentials in `.env`:
---
    YEKPAY_MERCHANT_ID=your-merchant-id  
    YEKPAY_SANDBOX=true
---
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
![Payment Flow](https://docs.yekpay.com/images/content/overview-cc5e71f9.png)
YekPay uses a three-step payment process:

1. Request Payment → Receive Authority
2. Redirect User → Gateway payment page
3. Verify Payment → Confirm transaction

This package mirrors that flow explicitly.

---

## Usage

### Request Payment

    use RostamSodagari\YekPay\Enums\Currency;
    use RostamSodagari\YekPay\Facade\Yekpay;
    use RostamSodagari\YekPay\DTO\RequestPaymentData;  
    
    $result = YekPay::request(new RequestPaymentData(
            fromCurrency: Currency::EUR,
            toCurrency: Currency::EUR,
            email: 'user@example.com',
            mobile: '+4474940000000',
            firstName: 'John',
            lastName: 'Doe',
            address: 'Address Here',
            postalCode: 'Postal Code',
            country: "United Kingdomm",
            city: "London",
            callback: 'http://verify-callback-here',
            orderNumber: 'unique-order-number',
            amount: 1000,
            description: 'Order #1001',
        ));
    
    if (! $result->ok() || ! $result->authority) {
        abort(400, $result->message());
    }

---

### Redirect User to Gateway

    return redirect()->away(
        Yekpay::startUrl($result->getAuthority())
    );

---

### Verify Payment (Callback)

    use Illuminate\Http\Request;  
    use RostamSodagari\YekPay\Facade\Yekpay;


    public function callback(Request $request)
    {
        $authority = (string) $request->input('Authority');
        
            if ($authority === '') {
                abort(400, 'Missing Authority');
            }
        
            $verify = Yekpay::verify($authority);
        
            if (! $verify->ok()) {
                abort(400, $verify->description);
            }
        
            return response()->json([
                'status'    => 'paid',
                'reference' => $verify->reference,
                'order'     => $verify->orderNo,
                'amount'    => $verify->amount,
            ]);
    }

---
## Test Cards (Sandbox Mode)

The following card details are **sandbox-only test cards** provided for integration testing with the [Yekpay](https://yekpay.com) payment gateway.

⚠️ **Important**
- These cards work **only in sandbox mode**
- They are **not real cards**
- Do **not** use them in production

| Card Name | Card Number | Expiration Date | CVC | Expected Result |
|----------|-------------|-----------------|-----|-----------------|
| John Doe | 5269 5522 3333 4445 | 2028/12 | 000 | Unsuccessful transaction |
| David Doe | 4022 7711 2222 3334 | 2028/12 | 000 | Successful transaction |
---
## Currency Enum

All supported currencies are defined as a PHP enum:

    use RostamSodagari\YekPay\Enums\Currency;

    Currency::EUR; // 978  
    Currency::IRR; // 364

Note on TRY currency:  
YekPay documentation contains conflicting TRY codes. This package uses the official appendix value by default, but you should confirm via sandbox before production use.

---

## Error Handling Philosophy

- Gateway responses are never trusted blindly
- ok() checks numeric result codes
- Raw gateway payload is preserved internally for logging

---

## Testing

This package is fully testable and ships with:

- Orchestra Testbench
- Guzzle MockHandler
- No real HTTP calls
---

## Security Best Practices

- Always verify payment before marking orders as paid
- Never trust callback parameters alone
- Persist gateway Reference for audit trails
- Enable sandbox during development

---

## License

MIT © Rostam Sodagari
