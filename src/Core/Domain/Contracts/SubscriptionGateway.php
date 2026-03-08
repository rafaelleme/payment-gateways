<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Contracts;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;

interface SubscriptionGateway
{
    public function createSubscription(Subscription $subscription): Subscription;

    public function getSubscription(string $subscriptionId): Subscription;

    public function cancelSubscription(string $subscriptionId): void;

    /** @return array<int, Payment> */
    public function getSubscriptionPayments(string $subscriptionId): array;
}
