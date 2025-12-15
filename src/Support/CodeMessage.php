<?php

namespace RostamSodagari\YekPay\Support;

final class CodeMessage
{
    public static function for(int $code): string
    {
        $key = "yekpay::yekpay.codes.$code";

        if (trans()->has($key)) {
            return trans($key);
        }

        return trans('yekpay::yekpay.unknown_code', ['code' => $code]);
    }
}
