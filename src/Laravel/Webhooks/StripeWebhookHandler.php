<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks;

use Illuminate\Contracts\Events\Dispatcher;
use Psr\Log\LoggerInterface;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\StripeWebhookEvent;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

class StripeWebhookHandler
{
    public function __construct(
        private Dispatcher $events,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(array $payload): void
    {
        $this->logger->info('Stripe webhook received', [
            'payload' => $payload,
        ]);

        $eventType = $payload['type']           ?? null;
        $data      = $payload['data']['object'] ?? [];

        if (!$eventType) {
            $this->logger->warning('Stripe webhook: no event type found in payload');

            return;
        }

        $this->logger->info('Processing Stripe webhook event', [
            'event_type' => $eventType,
        ]);

        try {
            $event = StripeWebhookEvent::tryFrom($eventType);
        } catch (\ValueError) {
            $this->logger->warning('Stripe webhook: invalid event type', [
                'event_type' => $eventType,
            ]);

            return;
        }

        if ($event === null) {
            $this->logger->info('Stripe webhook: event type not mapped', [
                'event_type' => $eventType,
            ]);

            return;
        }

        if ($event->isPaymentSuccess()) {
            $this->logger->info('Dispatching PaymentReceived event', [
                'event_type' => $eventType,
                'data'       => $data,
            ]);
            $this->events->dispatch(new PaymentReceived($data));
        } elseif ($event->isPaymentFailure()) {
            $this->logger->info('Dispatching PaymentRefused event (payment failure)', [
                'event_type' => $eventType,
                'data'       => $data,
            ]);
            $this->events->dispatch(new PaymentRefused($data));
        } elseif ($event->isPaymentDispute()) {
            $this->logger->info('Dispatching PaymentRefused event (payment dispute)', [
                'event_type' => $eventType,
                'data'       => $data,
            ]);
            $this->events->dispatch(new PaymentRefused($data));
        }
    }
}
