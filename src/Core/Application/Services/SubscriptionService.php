<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Application\Services;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;

class SubscriptionService
{
    public function __construct(
        protected SubscriptionGateway $gateway,
    ) {
    }

    public function create(Subscription $subscription): Subscription
    {
        return $this->gateway->createSubscription($subscription);
    }

    public function get(string $subscriptionId): Subscription
    {
        return $this->gateway->getSubscription($subscriptionId);
    }

    public function cancel(string $subscriptionId): void
    {
        $this->gateway->cancelSubscription($subscriptionId);
    }

    /** @return array<int, Payment> */
    public function payments(string $subscriptionId): array
    {
        return $this->gateway->getSubscriptionPayments($subscriptionId);
    }
}
