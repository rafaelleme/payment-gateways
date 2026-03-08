<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks\Events;

/**
 * Dispatched when a subscription is automatically cancelled after the grace period.
 * The consuming application should use this to revoke access, remove roles, etc.
 */
class SubscriptionCancelled
{
    public function __construct(
        public readonly string $gatewaySubscriptionId,
        public readonly string $gateway,
        public readonly ?int   $userId,
    ) {
    }
}
