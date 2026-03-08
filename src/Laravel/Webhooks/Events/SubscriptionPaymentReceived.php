<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks\Events;

/**
 * Dispatched when a subscription payment is successfully received or confirmed.
 * The consuming application should use this to grant access, assign roles, etc.
 */
class SubscriptionPaymentReceived
{
    /** @param array<string, mixed> $payment */
    public function __construct(
        public readonly array  $payment,
        public readonly string $gateway,
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
