<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Customers;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;

class StripeCustomerMapper
{
    /** @param array<string, mixed> $data */
    public function toCustomer(array $data): Customer
    {
        return new Customer(
            name:              (string) ($data['name'] ?? ''),
            email:             (string) ($data['email'] ?? ''),
            phone:             isset($data['phone']) ? (string) $data['phone'] : null,
            cpfCnpj:           isset($data['metadata']['cpfCnpj']) ? (string) $data['metadata']['cpfCnpj'] : null,
            id:                (string) $data['id'],
            externalReference: isset($data['metadata']['externalReference']) ? (int) $data['metadata']['externalReference'] : null,
        );
    }
}
