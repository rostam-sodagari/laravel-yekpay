<?php

namespace RostamSodagari\YekPay\DTO;

final class VerifyPaymentResult
{
    public function __construct(
        public readonly int $code,
        public readonly string $description,
        public readonly ?string $reference = null,
        public readonly ?string $gateway = null,
        public readonly ?string $orderNo = null,
        public readonly ?string $amount = null,
        public readonly ?array $raw = null,
    ) {}

    public function ok(): bool
    {
        return $this->code === 100;
    }
}
