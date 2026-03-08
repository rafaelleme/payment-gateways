<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Services;

use Rafaelleme\PaymentGateways\Core\Application\Services\SubscriptionService;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\CustomerRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;

class PersistentSubscriptionService
{
    public function __construct(
        private readonly SubscriptionService $service,
        private readonly SubscriptionRepositoryContract $repository,
        private readonly CustomerRepositoryContract $customerRepository,
        private readonly string $gateway,
    ) {
    }

    public function create(Subscription $subscription, ?int $userId = null): Subscription
    {
        $result = $this->service->create($subscription);

        $localCustomerId = $this->customerRepository->findLocalId(
            $this->gateway,
            $subscription->customerId->getValue(),
        );

        $this->repository->save($this->gateway, $result, $userId, $localCustomerId);

        return $result;
    }

    public function get(string $subscriptionId): Subscription
    {
        return $this->service->get($subscriptionId);
    }

    public function cancel(string $subscriptionId): void
    {
        $this->service->cancel($subscriptionId);

        $this->repository->updateStatus($this->gateway, $subscriptionId, 'CANCELLED');
    }

    /** @return array<int, Payment> */
    public function payments(string $subscriptionId): array
    {
        return $this->service->payments($subscriptionId);
    }
}
