<?php

namespace RostamSodagari\YekPay\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use RostamSodagari\YekPay\DTO\RequestPaymentData;
use RostamSodagari\YekPay\Enums\Currency;
use RostamSodagari\YekPay\Tests\TestCase;
use RostamSodagari\YekPay\YekPay;

final class YekPayClientTest extends TestCase
{
    public function test_it_requests_payment_with_expected_payload_and_parses_response(): void
    {
        config()->set('yekpay.merchant_id', 'MERCHANT-123');
        config()->set('yekpay.sandbox', true);

        // Mock API response for request:
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'Code' => 100,
                'Description' => 'Operation was successful',
                'Authority' => 'AUTH-XYZ',
            ], JSON_THROW_ON_ERROR)),
        ]);

        $history = [];
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $client = new Client(['handler' => $stack]);
        $this->app->instance(ClientInterface::class, $client);

        /** @var YekPay $yekpay */
        $yekpay = $this->app->make(YekPay::class);

        $dto = new RequestPaymentData(
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
            callback: 'https://example.test/callback',
            orderNumber: 'ORD-1001',
            amount: '1000000.00',
            description: 'Order #ORD-1001',
        );

        $result = $yekpay->request($dto);

        // Assertions on response parsing:
        $this->assertTrue($result->ok());
        $this->assertSame(100, $result->code);
        $this->assertSame('Operation was successful', $result->description);
        $this->assertSame('AUTH-XYZ', $result->authority);

        // Assertions on request:
        $this->assertCount(1, $history);
        $request = $history[0]['request'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/sandbox/request', $request->getUri()->getPath());

        // Guzzle form_params becomes urlencoded body.
        parse_str((string) $request->getBody(), $body);
        $this->assertSame('MERCHANT-123', $body['merchantId']);
        $this->assertSame('1000000.00', $body['amount']);
        $this->assertSame((string) Currency::IRR->value, $body['fromCurrencyCode']);
        $this->assertSame((string) Currency::EUR->value, $body['toCurrencyCode']);
        $this->assertSame('ORD-1001', $body['orderNumber']);
        $this->assertSame('https://example.test/callback', $body['callback']);
        $this->assertSame('John', $body['firstName']);
        $this->assertSame('Doe', $body['lastName']);
        $this->assertSame('john@example.com', $body['email']);
        $this->assertSame('+44123456789', $body['mobile']);
        $this->assertSame('No.1, Second.St', $body['address']);
        $this->assertSame('SW1A 1AA', $body['postalCode']);
        $this->assertSame('UK', $body['country']);
        $this->assertSame('London', $body['city']);
        $this->assertSame('Order #ORD-1001', $body['description']);
    }

    public function test_it_builds_start_url_correctly(): void
    {
        config()->set('yekpay.sandbox', true);

        /** @var YekPay $yekpay */
        $yekpay = $this->app->make(YekPay::class);

        $url = $yekpay->startUrl('AUTH-XYZ');

        $this->assertSame('https://api.ypsapi.com/api/sandbox/payment/AUTH-XYZ', $url);
    }

    public function test_it_verifies_payment_with_expected_payload_and_parses_response(): void
    {
        config()->set('yekpay.merchant_id', 'MERCHANT-123');
        config()->set('yekpay.sandbox', true);

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'Code' => 100,
                'Description' => 'Verified',
                'Reference' => 'REF-555',
                'Gateway' => 'YekPay',
                'OrderNo' => 'ORD-1001',
                'Amount' => '1000000.00',
            ], JSON_THROW_ON_ERROR)),
        ]);

        $history = [];
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $client = new Client(['handler' => $stack]);
        $this->app->instance(ClientInterface::class, $client);

        /** @var YekPay $yekpay */
        $yekpay = $this->app->make(YekPay::class);

        $verify = $yekpay->verify('AUTH-XYZ');

        $this->assertTrue($verify->ok());
        $this->assertSame(100, $verify->code);
        $this->assertSame('Verified', $verify->description);
        $this->assertSame('REF-555', $verify->reference);
        $this->assertSame('YekPay', $verify->gateway);
        $this->assertSame('ORD-1001', $verify->orderNo);
        $this->assertSame('1000000.00', $verify->amount);

        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/sandbox/verify', $request->getUri()->getPath());

        parse_str((string) $request->getBody(), $body);
        $this->assertSame('MERCHANT-123', $body['merchantId']);
        $this->assertSame('AUTH-XYZ', $body['authority']);
    }

    public function test_localized_message_returns_translation_when_present_and_falls_back_when_missing(): void
    {
        app()->setLocale('en');

        /** @var \RostamSodagari\YekPay\DTO\RequestPaymentResult $resultOk */
        $resultOk = new \RostamSodagari\YekPay\DTO\RequestPaymentResult(
            code: 100,
            description: 'whatever from api',
            authority: 'AUTH-XYZ'
        );

        $this->assertSame('Successful Operation.', $resultOk->message());

        $resultUnknown = new \RostamSodagari\YekPay\DTO\RequestPaymentResult(
            code: 9999,
            description: 'unknown',
            authority: null
        );

        $this->assertSame('Unknown gateway response code: 9999', $resultUnknown->message());
    }
    public function test_it_translates_known_error_codes(): void
    {
        app()->setLocale('en');

        $r = new \RostamSodagari\YekPay\DTO\RequestPaymentResult(
            code: -10,
            description: 'raw from api',
            authority: null
        );

        $this->assertSame('Your IP is restricted.', $r->message());
        $this->assertFalse($r->ok());
    }
    public function test_it_falls_back_for_unknown_codes(): void
    {
        app()->setLocale('en');

        $r = new \RostamSodagari\YekPay\DTO\RequestPaymentResult(
            code: 9999,
            description: 'raw from api',
            authority: null
        );

        $this->assertSame('Unknown gateway response code: 9999', $r->message());
    }

}
