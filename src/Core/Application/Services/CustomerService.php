<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Application\Services;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\CustomerGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;

class CustomerService
{
    public function __construct(
        protected CustomerGateway $gateway,
    ) {
    }

    public function create(Customer $customer): Customer
    {
        return $this->gateway->createCustomer($customer);
    }

    public function get(string $customerId): Customer
    {
        return $this->gateway->getCustomer($customerId);
    }
}
