<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\CustomerException;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;

class FakeGateway implements GatewayContract
{
    /** @var array<string, Payment> */
    private array $payments = [];

    /** @var array<string, Customer> */
    private array $customers = [];

    /** @var array<string, Subscription> */
    private array $subscriptions = [];

    /** @var array<string, array<int, Payment>> */
    private array $subscriptionPayments = [];

    private int $sequence = 1;

    // --- Payments ---

    public function createPayment(Payment $payment): Payment
    {
        $id = 'fake_pay_' . $this->sequence++;

        $created = new Payment(
            customerId:        $payment->customerId,
            value:             $payment->value,
            billingType:       $payment->billingType,
            dueDate:           $payment->dueDate,
            description:       $payment->description,
            externalReference: $payment->externalReference,
            id:                $id,
            status:            PaymentStatus::PENDING,
            invoiceUrl:        'https://fake.gateway/invoice/' . $id,
        );

        $this->payments[$id] = $created;

        return $created;
    }

    public function getPayment(string $paymentId): Payment
    {
        if (!isset($this->payments[$paymentId])) {
            throw new \RuntimeException("Payment [{$paymentId}] not found in FakeGateway.");
        }

        return $this->payments[$paymentId];
    }

    // --- Customers ---

    public function createCustomer(Customer $customer): Customer
    {
        $id = 'fake_cus_' . $this->sequence++;

        $created = new Customer(
            name:              $customer->name,
            email:             $customer->email,
            phone:             $customer->phone,
            cpfCnpj:           $customer->cpfCnpj,
            id:                $id,
            externalReference: $customer->externalReference,
        );

        $this->customers[$id] = $created;

        return $created;
    }

    public function getCustomer(string $customerId): Customer
    {
        if (!isset($this->customers[$customerId])) {
            throw CustomerException::notFound($customerId);
        }

        return $this->customers[$customerId];
    }

    // --- Subscriptions ---

    public function createSubscription(Subscription $subscription): Subscription
    {
        $customerId = $subscription->customerId->getValue();

        if (!isset($this->customers[$customerId])) {
            throw CustomerException::notFound($customerId);
        }

        $id = 'fake_sub_' . $this->sequence++;

        $created = new Subscription(
            customerId:        $subscription->customerId,
            value:             $subscription->value,
            billingType:       $subscription->billingType,
            cycle:             $subscription->cycle,
            nextDueDate:       $subscription->nextDueDate,
            description:       $subscription->description,
            externalReference: $subscription->externalReference,
            id:                $id,
            status:            SubscriptionStatus::ACTIVE,
        );

        $this->subscriptions[$id]        = $created;
        $this->subscriptionPayments[$id] = [];

        return $created;
    }

    public function getSubscription(string $subscriptionId): Subscription
    {
        if (!isset($this->subscriptions[$subscriptionId])) {
            throw SubscriptionException::subscriptionNotFound($subscriptionId);
        }

        return $this->subscriptions[$subscriptionId];
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        if (!isset($this->subscriptions[$subscriptionId])) {
            throw SubscriptionException::subscriptionNotFound($subscriptionId);
        }

        $existing = $this->subscriptions[$subscriptionId];

        $this->subscriptions[$subscriptionId] = new Subscription(
            customerId:        $existing->customerId,
            value:             $existing->value,
            billingType:       $existing->billingType,
            cycle:             $existing->cycle,
            nextDueDate:       $existing->nextDueDate,
            description:       $existing->description,
            externalReference: $existing->externalReference,
            id:                $existing->id,
            status:            SubscriptionStatus::INACTIVE,
        );
    }

    /** @return array<int, Payment> */
    public function getSubscriptionPayments(string $subscriptionId): array
    {
        if (!isset($this->subscriptions[$subscriptionId])) {
            throw SubscriptionException::subscriptionNotFound($subscriptionId);
        }

        return $this->subscriptionPayments[$subscriptionId] ?? [];
    }

    // --- Test helpers ---

    public function hasPayment(string $paymentId): bool
    {
        return isset($this->payments[$paymentId]);
    }

    public function hasCustomer(string $customerId): bool
    {
        return isset($this->customers[$customerId]);
    }

    public function hasSubscription(string $subscriptionId): bool
    {
        return isset($this->subscriptions[$subscriptionId]);
    }

    /** @return array<string, Payment> */
    public function allPayments(): array
    {
        return $this->payments;
    }

    /** @return array<string, Subscription> */
    public function allSubscriptions(): array
    {
        return $this->subscriptions;
    }

    public function reset(): void
    {
        $this->payments             = [];
        $this->customers            = [];
        $this->subscriptions        = [];
        $this->subscriptionPayments = [];
        $this->sequence             = 1;
    }
}
