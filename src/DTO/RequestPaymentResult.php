<?php

namespace RostamSodagari\YekPay\DTO;

use RostamSodagari\YekPay\Enums\ResultCode;
use RostamSodagari\YekPay\Support\CodeMessage;

/**
 *
 */
final class RequestPaymentResult
{
    /**
     * @param int         $code
     * @param string      $description
     * @param string|null $authority
     */
    public function __construct(
        public readonly int $code,
        public readonly string $description, // raw from API
        public readonly ?string $authority,
    ) {}

    /**
     * @return bool
     */
    public function ok(): bool
    {
        return ResultCode::from($this->code)?->isOk() ?? false;
    }


    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getAuthority(): ?string
    {
        return $this->authority;
    }

}

