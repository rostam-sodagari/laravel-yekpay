<?php

namespace RostamSodagari\YekPay\DTO;

final class VerifyPaymentResult
{
    /**
     * @param int         $code
     * @param string      $description
     * @param string|null $reference
     * @param string|null $gateway
     * @param string|null $orderNo
     * @param string|null $amount
     * @param array|null  $raw
     */
    public function __construct(
        public readonly int $code,
        public readonly string $description,
        public readonly ?string $reference = null,
        public readonly ?string $gateway = null,
        public readonly ?string $orderNo = null,
        public readonly ?string $amount = null,
        public readonly ?array $raw = null,
    ) {}

    /**
     * @return bool
     */
    public function ok(): bool
    {
        return $this->code === 100;
    }
}
