<?php

namespace RostamSodagari\YekPay\Enums;

enum ResultCode: int
{
    case PARAMETERS_INCOMPLETE = -1;
    case MERCHANT_INCORRECT    = -2;
    case MERCHANT_INACTIVE     = -3;
    case ORDER_ID_INVALID      = -7;
    case CURRENCY_INVALID      = -8;
    case AMOUNT_INVALID        = -9;
    case IP_RESTRICTED         = -10;
    case UNKNOWN_ERROR         = -100;

    case SUCCESS               = 100;

    public function     isOk(): bool
    {
        return $this === self::SUCCESS;
    }
}
