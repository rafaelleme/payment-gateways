<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\CustomerGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;

class AsaasCustomerGateway implements CustomerGateway
{
    public function __construct(
        private readonly AsaasClient $client,
        private readonly AsaasCustomerMapper $mapper = new AsaasCustomerMapper(),
    ) {
    }

    public function createCustomer(Customer $customer): Customer
    {
        $data = $this->client->createCustomer([
            'name'              => $customer->name,
            'email'             => $customer->email,
            'phone'             => $customer->phone,
            'cpfCnpj'           => $customer->cpfCnpj,
            'externalReference' => $customer->externalReference,
        ]);

        return $this->mapper->toCustomer($data);
    }

    public function getCustomer(string $customerId): Customer
    {
        $data = $this->client->getCustomer($customerId);

        return $this->mapper->toCustomer($data);
    }
}
