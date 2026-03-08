<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\Customers\AsaasCustomerMapper;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\Payments\AsaasPaymentMapper;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\Subscriptions\AsaasSubscriptionMapper;

class AsaasGateway implements GatewayContract
{
    private readonly AsaasPaymentMapper $paymentMapper;
    private readonly AsaasCustomerMapper $customerMapper;
    private readonly AsaasSubscriptionMapper $subscriptionMapper;

    public function __construct(
        private readonly AsaasClient $client,
    ) {
        $this->paymentMapper      = new AsaasPaymentMapper();
        $this->customerMapper     = new AsaasCustomerMapper();
        $this->subscriptionMapper = new AsaasSubscriptionMapper();
    }

    // --- Payments ---

    public function createPayment(Payment $payment): Payment
    {
        $data = $this->client->createPayment([
            'customer'          => $payment->customerId->getValue(),
            'billingType'       => $payment->billingType->value,
            'value'             => $payment->value->getAmount(),
            'dueDate'           => $payment->dueDate,
            'description'       => $payment->description,
            'externalReference' => $payment->externalReference,
        ]);

        return $this->paymentMapper->toPayment($data);
    }

    public function getPayment(string $paymentId): Payment
    {
        $data = $this->client->getPayment($paymentId);

        return $this->paymentMapper->toPayment($data);
    }

    // --- Customers ---

    public function createCustomer(Customer $customer): Customer
    {
        $data = $this->client->createCustomer([
            'name'              => $customer->name,
            'email'             => $customer->email,
            'phone'             => $customer->phone,
            'cpfCnpj'           => $customer->cpfCnpj,
            'externalReference' => $customer->externalReference,
        ]);

        return $this->customerMapper->toCustomer($data);
    }

    public function getCustomer(string $customerId): Customer
    {
        $data = $this->client->getCustomer($customerId);

        return $this->customerMapper->toCustomer($data);
    }

    // --- Subscriptions ---

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
