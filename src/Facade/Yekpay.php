<?php

namespace RostamSodagari\YekPay\Facade;

use Illuminate\Support\Facades\Facade;
use \RostamSodagari\YekPay\DTO\RequestPaymentData;
use \RostamSodagari\YekPay\DTO\VerifyPaymentResult;
use \RostamSodagari\YekPay\DTO\RequestPaymentResult;

/**
 * Class Yekpay
 *
 * @method static RequestPaymentResult request(RequestPaymentData $requestPaymentData)
 * @method static string startUrl(string $authority)
 * @method static VerifyPaymentResult verify(string $authority)
 *
 * @package RostamSodagari\YekPay\Facade
 * @see     RostamSodagari\YekPay
 */
class Yekpay extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'YekPay';
    }
}
