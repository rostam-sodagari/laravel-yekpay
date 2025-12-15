<?php

namespace RostamSodagari\YekPay\DTO;

use RostamSodagari\YekPay\Enums\ResultCode;
use RostamSodagari\YekPay\Support\CodeMessage;

final class RequestPaymentResult
{
    public function __construct(
        public readonly int $code,
        public readonly string $description, // raw from API
        public readonly ?string $authority,
    ) {}

    public function ok(): bool
    {
        return ResultCode::from($this->code)?->isOk() ?? false;
    }

    public function message(): string
    {
        return CodeMessage::for($this->code);
    }
}

