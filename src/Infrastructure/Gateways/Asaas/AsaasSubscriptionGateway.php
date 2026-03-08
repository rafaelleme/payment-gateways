<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;

class AsaasSubscriptionGateway implements SubscriptionGateway
{
    public function __construct(
        private readonly AsaasClient $client,
        private readonly AsaasSubscriptionMapper $subscriptionMapper = new AsaasSubscriptionMapper(),
        private readonly AsaasPaymentMapper $paymentMapper = new AsaasPaymentMapper(),
    ) {
    }

    public function createSubscription(Subscription $subscription): Subscription
    {
        $data = $this->client->createSubscription([
            'customer'          => $subscription->customerId->getValue(),
            'billingType'       => $subscription->billingType->value,
            'value'             => $subscription->value->getAmount(),
            'cycle'             => $subscription->cycle->value,
            'nextDueDate'       => $subscription->nextDueDate,
            'description'       => $subscription->description,
            'externalReference' => $subscription->externalReference,
        ]);

        if (isset($data['errors'])) {
            /** @var array<int, array<string, string>> $errors */
            $errors  = $data['errors'];
            $message = $errors[0]['description'] ?? 'Unknown error';
            throw SubscriptionException::apiError($message);
        }

        return $this->subscriptionMapper->toSubscription($data);
    }

    public function getSubscription(string $subscriptionId): Subscription
    {
        $data = $this->client->getSubscription($subscriptionId);

        if (empty($data['id'])) {
            throw SubscriptionException::subscriptionNotFound($subscriptionId);
        }

        return $this->subscriptionMapper->toSubscription($data);
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        $data = $this->client->cancelSubscription($subscriptionId);

        if (isset($data['errors'])) {
            /** @var array<int, array<string, string>> $errors */
            $errors  = $data['errors'];
            $message = $errors[0]['description'] ?? 'Unknown error';
            throw SubscriptionException::apiError($message);
        }
    }

    /** @return array<int, Payment> */
    public function getSubscriptionPayments(string $subscriptionId): array
    {
        $data = $this->client->getSubscriptionPayments($subscriptionId);

        /** @var array<int, array<string, mixed>> $items */
        $items = $data['data'] ?? [];

        return array_map(
            fn (array $item) => $this->paymentMapper->toPayment($item),
            $items,
        );
    }
}
