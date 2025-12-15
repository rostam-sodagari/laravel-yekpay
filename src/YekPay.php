<?php

namespace RostamSodagari\YekPay;

use GuzzleHttp\ClientInterface;
use RostamSodagari\YekPay\DTO\RequestPaymentData;
use RostamSodagari\YekPay\DTO\RequestPaymentResult;
use RostamSodagari\YekPay\DTO\VerifyPaymentResult;
use RostamSodagari\YekPay\Exceptions\YekPayException;

final class YekPay
{
    public function __construct(
        private readonly ClientInterface $http,
        private readonly string $merchantId,
        private readonly array $endpoints,
    ) {}

    public function request(RequestPaymentData $data): RequestPaymentResult
    {
        $res = $this->http->request('POST', $this->endpoints['request'], [
            'form_params' => $data->toFormParams($this->merchantId),
            'headers' => ['Accept' => 'application/json'],
        ]);

        $body = (string) $res->getBody();
        $json = json_decode($body, true);

        if (!is_array($json)) {
            throw new YekPayException('Invalid JSON response from YekPay request endpoint.');
        }

        return new RequestPaymentResult(
            code: (int) ($json['Code'] ?? 0),
            description: (string) ($json['Description'] ?? 'Unknown'),
            authority: isset($json['Authority']) ? (string) $json['Authority'] : null,
        );
    }

    public function startUrl(string $authority): string
    {
        return str_replace('{AUTHORITY}', $authority, $this->endpoints['start']);
    }

    public function verify(string $authority): VerifyPaymentResult
    {
        $res = $this->http->request('POST', $this->endpoints['verify'], [
            'form_params' => [
                'merchantId' => $this->merchantId,
                'authority'  => $authority,
            ],
            'headers' => ['Accept' => 'application/json'],
        ]);

        $body = (string) $res->getBody();
        $json = json_decode($body, true);

        if (!is_array($json)) {
            throw new YekPayException('Invalid JSON response from YekPay verify endpoint.');
        }

        return new VerifyPaymentResult(
            code: (int) ($json['Code'] ?? 0),
            description: (string) ($json['Description'] ?? 'Unknown'),
            reference: $json['Reference'] ?? null,
            gateway: $json['Gateway'] ?? null,
            orderNo: $json['OrderNo'] ?? null,
            amount: $json['Amount'] ?? null,
            raw: $json,
        );
    }
}
