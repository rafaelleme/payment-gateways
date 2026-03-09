<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Rafaelleme\PaymentGateways\Core\Application\Services\CustomerService;
use Rafaelleme\PaymentGateways\Core\Application\Services\PaymentService;
use Rafaelleme\PaymentGateways\Core\Application\Services\SubscriptionService;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\CustomerRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasClient;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;
use Rafaelleme\PaymentGateways\Laravel\Commands\InstallCommand;
use Rafaelleme\PaymentGateways\Laravel\Repositories\EloquentCustomerRepository;
use Rafaelleme\PaymentGateways\Laravel\Repositories\EloquentPaymentRepository;
use Rafaelleme\PaymentGateways\Laravel\Repositories\EloquentSubscriptionRepository;
use Rafaelleme\PaymentGateways\Laravel\Services\PersistentCustomerService;
use Rafaelleme\PaymentGateways\Laravel\Services\PersistentPaymentService;
use Rafaelleme\PaymentGateways\Laravel\Services\PersistentSubscriptionService;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Listeners\UpdatePaymentStatusOnWebhook;
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

            $manager->register('asaas', function () use ($config, $app) {
                $asaasConfig = $config['gateways']['asaas'];

                return new AsaasGateway(
                    client: new AsaasClient(
                        apiKey:  $asaasConfig['api_key'],
                        baseUrl: $asaasConfig['base_url'] ?? 'https://api.asaas.com',
                    ),
                    logger: $app->make('log'),
                );
            });

            return $manager;
        });

        $this->app->bind(GatewayContract::class, function ($app) {
            return $app->make(GatewayManager::class)->driver();
        });

        // --- Repositories ---
        $this->app->bind(CustomerRepositoryContract::class, EloquentCustomerRepository::class);
        $this->app->bind(PaymentRepositoryContract::class, EloquentPaymentRepository::class);
        $this->app->bind(SubscriptionRepositoryContract::class, EloquentSubscriptionRepository::class);

        // --- Persistent services ---
        $this->app->bind(PersistentCustomerService::class, function ($app) {
            return new PersistentCustomerService(
                service:    new CustomerService($app->make(GatewayContract::class)),
                repository: $app->make(CustomerRepositoryContract::class),
                gateway:    $app['config']['payment-gateways']['default'] ?? 'asaas',
            );
        });

        $this->app->bind(PersistentSubscriptionService::class, function ($app) {
            return new PersistentSubscriptionService(
                service:            new SubscriptionService($app->make(GatewayContract::class)),
                repository:         $app->make(SubscriptionRepositoryContract::class),
                customerRepository: $app->make(CustomerRepositoryContract::class),
                gateway:            $app['config']['payment-gateways']['default'] ?? 'asaas',
            );
        });

        $this->app->bind(PersistentPaymentService::class, function ($app) {
            return new PersistentPaymentService(
                service:                $app->make(PaymentService::class),
                repository:             $app->make(PaymentRepositoryContract::class),
                subscriptionRepository: $app->make(SubscriptionRepositoryContract::class),
                gateway:                $app['config']['payment-gateways']['default'] ?? 'asaas',
            );
        });

        // --- Webhook handler ---
        $this->app->bind(Webhooks\AsaasWebhookHandler::class, function ($app) {
            return new Webhooks\AsaasWebhookHandler(
                events: $app->make(Dispatcher::class),
            );
        });

        // --- Webhook listener ---
        $this->app->bind(Webhooks\Listeners\UpdatePaymentStatusOnWebhook::class, function ($app) {
            return new Webhooks\Listeners\UpdatePaymentStatusOnWebhook(
                paymentRepository:      $app->make(PaymentRepositoryContract::class),
                subscriptionRepository: $app->make(SubscriptionRepositoryContract::class),
                events:                 $app->make(Dispatcher::class),
                gateway:                $app['config']['payment-gateways']['default'] ?? 'asaas',
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../../config/payment-gateways.php' => config_path('payment-gateways.php'),
            ], 'payment-gateways-config');

            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'payment-gateways-migrations');
        }

        $this->loadRoutesFrom(__DIR__ . '/routes/webhooks.php');

        // Register webhook listener for DB persistence
        /** @var Dispatcher $events */
        $events = $this->app->make(Dispatcher::class);
        $events->listen(PaymentReceived::class, [UpdatePaymentStatusOnWebhook::class, 'handleReceived']);
        $events->listen(PaymentOverdue::class, [UpdatePaymentStatusOnWebhook::class, 'handleOverdue']);
        $events->listen(PaymentRefused::class, [UpdatePaymentStatusOnWebhook::class, 'handleRefused']);
    }
}
