<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Services;

use Rafaelleme\PaymentGateways\Core\Application\Services\PaymentService;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;

class PersistentPaymentService
{
    public function __construct(
        private readonly PaymentService $service,
        private readonly PaymentRepositoryContract $repository,
        private readonly SubscriptionRepositoryContract $subscriptionRepository,
        private readonly string $gateway,
    ) {
    }

    public function create(Payment $payment, ?int $userId = null): Payment
    {
        $result = $this->service->create($payment);

        $localSubscriptionId = null;

        if ($result->externalReference !== null) {
            $localSubscriptionId = $this->subscriptionRepository->findLocalId(
                $this->gateway,
                $result->externalReference,
            );
        }

        $this->repository->save($this->gateway, $result, $userId, $localSubscriptionId);

        return $result;
    }

    public function get(string $paymentId): Payment
    {
        return $this->service->get($paymentId);
    }
}
