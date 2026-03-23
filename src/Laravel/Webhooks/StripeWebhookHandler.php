<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks;

use Illuminate\Contracts\Events\Dispatcher;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\StripeWebhookEvent;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

class StripeWebhookHandler
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(array $payload): void
    {
        $eventType = $payload['type']           ?? null;
        $data      = $payload['data']['object'] ?? [];

        if (!$eventType) {
            return;
        }

        try {
            $event = StripeWebhookEvent::tryFrom($eventType);
        } catch (\ValueError) {
            return;
        }

        if ($event === null) {
            return;
        }

        if ($event->isPaymentSuccess()) {
            $this->events->dispatch(new PaymentReceived($data));
        } elseif ($event->isPaymentFailure()) {
            $this->events->dispatch(new PaymentRefused($data));
        } elseif ($event->isPaymentDispute()) {
            $this->events->dispatch(new PaymentRefused($data));
        }
    }
}
