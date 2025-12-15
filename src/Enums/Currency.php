<?php

namespace RostamSodagari\YekPay\Enums;

enum Currency: int
{
    case EUR = 978;
    case IRR = 364;
    case CHF = 756;
    case AED = 784;
    case CNY = 156;
    case GBP = 826;
    case JPY = 392;
    case RUB = 643;

    /**
     * ⚠ Docs are inconsistent for TRY (Appendix vs example).
     */
    case TRY = 494;
}
