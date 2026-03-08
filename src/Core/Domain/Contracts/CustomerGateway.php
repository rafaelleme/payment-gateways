<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Contracts;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;

interface CustomerGateway
{
    public function createCustomer(Customer $customer): Customer;

    public function getCustomer(string $customerId): Customer;
}
