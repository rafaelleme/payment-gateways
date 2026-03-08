<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks;

use Illuminate\Contracts\Events\Dispatcher;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

readonly class AsaasWebhookHandler
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function handle(array $payload): void
    {
        $event   = (string) ($payload['event'] ?? '');
        $payment = (array) ($payload['payment'] ?? []);

        match ($event) {
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED' => $this->events->dispatch(new PaymentReceived($payment)),
            'PAYMENT_OVERDUE'   => $this->events->dispatch(new PaymentOverdue($payment)),
            'PAYMENT_REFUSED'   => $this->events->dispatch(new PaymentRefused($payment)),
            default             => null,
        };
    }
}
