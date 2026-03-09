<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks;

use Illuminate\Contracts\Events\Dispatcher;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasWebhookEvent;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

class AsaasWebhookHandler
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function handle(array $payload): void
    {
        $event   = AsaasWebhookEvent::tryFrom((string) ($payload['event'] ?? ''));
        $payment = (array) ($payload['payment'] ?? []);

        match ($event?->toDispatchEvent()) {
            'received' => $this->events->dispatch(new PaymentReceived($payment)),
            'overdue'  => $this->events->dispatch(new PaymentOverdue($payment)),
            'refused'  => $this->events->dispatch(new PaymentRefused($payment)),
            default    => null,
        };
    }
}
