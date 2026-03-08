<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel;

use Illuminate\Support\ServiceProvider;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasClient;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\AsaasWebhookHandler;
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
            $manager->register('asaas', function () use ($config, $app) {
                $asaasConfig = $config['gateways']['asaas'];

                return new AsaasGateway(
                    client: new AsaasClient(
                        apiKey:  $asaasConfig['api_key'],
                        baseUrl: $asaasConfig['base_url'] ?? 'https://api.asaas.com/v3',
                    ),
                    logger: $app->make('log'),
                );
            });

            return $manager;
        });

        // Bind the GatewayContract to the default driver
        $this->app->bind(GatewayContract::class, function ($app) {
            return $app->make(GatewayManager::class)->driver();
        });

        $this->app->bind(AsaasWebhookHandler::class, function ($app) {
            return new AsaasWebhookHandler($app->make('events'));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/payment-gateways.php' => config_path('payment-gateways.php'),
            ], 'payment-gateways-config');
        }

        $this->loadRoutesFrom(__DIR__ . '/routes/webhooks.php');
    }
}
