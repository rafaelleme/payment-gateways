<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Contracts;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;

interface CustomerRepositoryContract
{
    public function save(string $gateway, Customer $customer, ?int $userId = null): void;

    public function findByGatewayId(string $gateway, string $gatewayCustomerId): ?Customer;

    public function findLocalId(string $gateway, string $gatewayCustomerId): ?int;
}
