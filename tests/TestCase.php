<?php

namespace RostamSodagari\YekPay\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RostamSodagari\YekPay\YekPayServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param $app
     *
     * @return \class-string[]
     */
    protected function getPackageProviders($app): array
    {
        return [YekPayServiceProvider::class];
    }
}
