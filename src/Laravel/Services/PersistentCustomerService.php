<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Services;

use Rafaelleme\PaymentGateways\Core\Application\Services\CustomerService;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\CustomerRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;

class PersistentCustomerService
{
    public function __construct(
        private readonly CustomerService $service,
        private readonly CustomerRepositoryContract $repository,
        private readonly string $gateway,
    ) {
    }

    public function create(Customer $customer, ?int $userId = null): Customer
    {
        $result = $this->service->create($customer);

        $this->repository->save($this->gateway, $result, $userId);

        return $result;
    }

    public function get(string $customerId): Customer
    {
        return $this->service->get($customerId);
    }
}
