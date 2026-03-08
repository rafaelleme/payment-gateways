<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Contracts;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\CreditCardToken;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardData;

interface GatewayContract
{
    // --- Payments ---

    public function createPayment(Payment $payment): Payment;

    public function getPayment(string $paymentId): Payment;

    // --- Customers ---

    public function createCustomer(Customer $customer): Customer;

    public function getCustomer(string $customerId): Customer;

    // --- Subscriptions ---

    public function createSubscription(Subscription $subscription): Subscription;

    public function getSubscription(string $subscriptionId): Subscription;

    public function cancelSubscription(string $subscriptionId): void;

    /** @return array<int, Payment> */
    public function getSubscriptionPayments(string $subscriptionId): array;

    // --- Credit Card ---

    public function tokenizeCreditCard(string $customerId, CreditCardData $cardData): CreditCardToken;
}
