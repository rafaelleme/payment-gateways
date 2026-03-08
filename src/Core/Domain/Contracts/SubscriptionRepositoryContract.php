<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Contracts;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;

interface SubscriptionRepositoryContract
{
    public function save(string $gateway, Subscription $subscription, ?int $userId = null, ?int $localCustomerId = null): void;

    public function updateStatus(string $gateway, string $gatewaySubscriptionId, string $status): void;

    public function findByGatewayId(string $gateway, string $gatewaySubscriptionId): ?Subscription;

    public function findLocalId(string $gateway, string $gatewaySubscriptionId): ?int;
}
