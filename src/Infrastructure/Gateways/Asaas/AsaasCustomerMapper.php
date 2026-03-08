<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;

class AsaasCustomerMapper
{
    /** @param array<string, mixed> $data */
    public function toCustomer(array $data): Customer
    {
        return new Customer(
            name:              (string) ($data['name'] ?? ''),
            email:             (string) ($data['email'] ?? ''),
            phone:             isset($data['phone']) ? (string) $data['phone'] : null,
            cpfCnpj:           isset($data['cpfCnpj']) ? (string) $data['cpfCnpj'] : null,
            id:                (string) $data['id'],
            externalReference: isset($data['externalReference']) ? (string) $data['externalReference'] : null,
        );
    }
}
