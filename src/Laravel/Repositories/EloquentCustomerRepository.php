<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Repositories;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\CustomerRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Laravel\Models\GatewayCustomer;

class EloquentCustomerRepository implements CustomerRepositoryContract
{
    public function save(string $gateway, Customer $customer, ?int $userId = null): void
    {
        GatewayCustomer::updateOrCreate(
            [
                'gateway'             => $gateway,
                'gateway_customer_id' => $customer->id,
            ],
            [
                'user_id'  => $userId,
                'name'     => $customer->name,
                'email'    => $customer->email,
                'document' => $customer->cpfCnpj,
            ],
        );
    }

    public function findByGatewayId(string $gateway, string $gatewayCustomerId): ?Customer
    {
        $record = GatewayCustomer::where('gateway', $gateway)
            ->where('gateway_customer_id', $gatewayCustomerId)
            ->first();

        if ($record === null) {
            return null;
        }

        return new Customer(
            name:      $record->name,
            email:     $record->email,
            cpfCnpj:   $record->document,
            id:        $record->gateway_customer_id,
        );
    }

    public function findLocalId(string $gateway, string $gatewayCustomerId): ?int
    {
        $record = GatewayCustomer::where('gateway', $gateway)
            ->where('gateway_customer_id', $gatewayCustomerId)
            ->first();

        return $record?->id;
    }
}
