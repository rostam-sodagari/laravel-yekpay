<?php

namespace RostamSodagari\YekPay;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

final class YekPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/yekpay.php', 'yekpay');
        $this->app->bind(ClientInterface::class, function () {
            $cfg = config('yekpay');

            return new Client([
                'connect_timeout' => $cfg['timeouts']['connect'] ?? 5,
                'timeout' => $cfg['timeouts']['request'] ?? 20,
            ]);
        });

        $this->app->singleton('YekPay', function ($app) {
            $cfg = config('yekpay');

            $mode = !empty($cfg['sandbox']) ? 'sandbox' : 'production';
            $endpoints = $cfg['endpoints'][$mode];

            return new YekPay(
                http: $app->make(ClientInterface::class),
                merchantId: (string) ($cfg['merchant_id'] ?? ''),
                endpoints: $endpoints,
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/yekpay.php' => config_path('yekpay.php'),
        ], 'yekpay-config');

    }
}
