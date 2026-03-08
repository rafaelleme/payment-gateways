<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel;

use Illuminate\Support\ServiceProvider;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;
use Rafaelleme\PaymentGateways\Support\GatewayManager;

class PaymentGatewaysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/payment-gateways.php',
            'payment-gateways',
        );

        $this->app->singleton(GatewayManager::class, function ($app) {
            $config  = $app['config']['payment-gateways'];
            $default = $config['default'] ?? 'asaas';

            $manager = new GatewayManager($default);

            // Register built-in gateways
            $manager->register('asaas', function () use ($config) {
                $asaasConfig = $config['gateways']['asaas'];

                $baseUrl = $asaasConfig['sandbox']
                    ? 'https://sandbox.asaas.com/api/v3'
                    : ($asaasConfig['base_url'] ?? 'https://api.asaas.com/v3');

                return new AsaasGateway(
                    apiKey:  $asaasConfig['api_key'],
                    baseUrl: $baseUrl,
                );
            });

            return $manager;
        });

        // Bind the PaymentGateway contract to the default driver
        $this->app->bind(PaymentGateway::class, function ($app) {
            return $app->make(GatewayManager::class)->driver();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/payment-gateways.php' => config_path('payment-gateways.php'),
            ], 'payment-gateways-config');
        }
    }
}
