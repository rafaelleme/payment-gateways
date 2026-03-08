<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks\Events;

/**
 * Dispatched when a subscription payment fails or becomes overdue.
 * The consuming application can use this to notify the user, start a grace period, etc.
 */
class SubscriptionPaymentFailed
{
    /** @param array<string, mixed> $payment */
    public function __construct(
        public readonly array  $payment,
        public readonly string $gateway,
        public readonly string $reason, // 'OVERDUE' | 'REFUSED'
    ) {
    }

    public function subscriptionId(): string
    {
        return (string) ($this->payment['subscription'] ?? '');
    }

    public function paymentId(): string
    {
        return (string) ($this->payment['id'] ?? '');
    }
}
