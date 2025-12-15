<?php

namespace RostamSodagari\YekPay\DTO;
use RostamSodagari\YekPay\Enums\Currency;

final class RequestPaymentData
{
    public function __construct(
        public readonly Currency $fromCurrency,
        public readonly Currency $toCurrency,
        public readonly string $email,
        public readonly string $mobile,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $address,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly string $city,
        public readonly string $callback,
        public readonly string $orderNumber,
        public readonly string $amount,          // Keep string; docs show decimal(15,2)
        public readonly ?string $description = null,
    ) {}

    public function toFormParams(string $merchantId): array
    {
        // Docs say all required except description. :contentReference[oaicite:8]{index=8}
        return array_filter([
            'merchantId'       => $merchantId,
            'amount'           => $this->amount,
            'fromCurrencyCode' => $this->fromCurrency->value,
            'toCurrencyCode'   => $this->toCurrency->value,
            'orderNumber'      => $this->orderNumber,
            'callback'         => $this->callback,
            'firstName'        => $this->firstName,
            'lastName'         => $this->lastName,
            'email'            => $this->email,
            'mobile'           => $this->mobile,
            'address'          => $this->address,
            'postalCode'       => $this->postalCode,
            'country'          => $this->country,
            'city'             => $this->city,
            'description'      => $this->description,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
