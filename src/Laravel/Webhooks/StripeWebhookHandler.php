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

        // Normalize Stripe data to standard format
        $normalizedPayment = $this->normalizePayment($data);

        if ($event->isPaymentSuccess()) {
            $this->logger->info('Dispatching PaymentReceived event', [
                'event_type' => $eventType,
                'data'       => $normalizedPayment,
            ]);
            $this->events->dispatch(new PaymentReceived($normalizedPayment));
        } elseif ($event->isPaymentFailure()) {
            $this->logger->info('Dispatching PaymentRefused event (payment failure)', [
                'event_type' => $eventType,
                'data'       => $normalizedPayment,
            ]);
            $this->events->dispatch(new PaymentRefused($normalizedPayment));
        } elseif ($event->isPaymentDispute()) {
            $this->logger->info('Dispatching PaymentRefused event (payment dispute)', [
                'event_type' => $eventType,
                'data'       => $normalizedPayment,
            ]);
            $this->events->dispatch(new PaymentRefused($normalizedPayment));
        }
    }

    /**
     * Normalizes Stripe payment data to standard format expected by listeners.
     * Maps Stripe invoice/charge data to id and subscription fields.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizePayment(array $data): array
    {
        $normalized = $data;

        // For invoices, map invoice ID
        if (isset($data['object']) && $data['object'] === 'invoice') {
            $normalized['id'] = $data['id'] ?? null;

            // Extract subscription ID from nested parent structure
            $subscriptionId = $data['parent']['subscription_details']['subscription'] ?? null;
            if ($subscriptionId === null) {
                // Fallback to subscription field if present
                $subscriptionId = $data['subscription'] ?? null;
            }

            $normalized['subscription'] = $subscriptionId;
        } elseif (isset($data['object']) && $data['object'] === 'charge') {
            // For charges, map charge ID
            $normalized['id']           = $data['id']                ?? null;
            $normalized['subscription'] = $data['invoice'] ?? null; // invoice ID as subscription reference
        }

        return $normalized;
    }
}
