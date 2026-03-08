<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Application\Services\CustomerService;
use Rafaelleme\PaymentGateways\Core\Application\Services\SubscriptionService;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\FakeGateway;

class SubscriptionServiceTest extends TestCase
{
    private FakeGateway $gateway;
    private CustomerService $customerService;
    private SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        $this->gateway             = new FakeGateway();
        $this->customerService     = new CustomerService($this->gateway);
        $this->subscriptionService = new SubscriptionService($this->gateway);
    }

    private function createCustomer(): Customer
    {
        return $this->customerService->create(new Customer(
            name:  'John Doe',
            email: 'john@example.com',
        ));
    }

    private function makeSubscription(string $customerId): Subscription
    {
        return new Subscription(
            customerId:  new CustomerId($customerId),
            value:       new Money(29.90),
            billingType: BillingType::CREDIT_CARD,
            cycle:       SubscriptionCycle::MONTHLY,
            nextDueDate: '2026-04-01',
            description: 'Plano Pro',
        );
    }

    public function test_create_subscription_with_success(): void
    {
        $customer     = $this->createCustomer();
        $subscription = $this->subscriptionService->create($this->makeSubscription($customer->id));

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertTrue($subscription->isPersisted());
        $this->assertSame(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertTrue($subscription->isActive());
        $this->assertSame(29.90, $subscription->value->getAmount());
        $this->assertSame(SubscriptionCycle::MONTHLY, $subscription->cycle);
    }

    public function test_create_subscription_fails_if_customer_not_found(): void
    {
        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Customer [non_existent] not found.');

        $this->subscriptionService->create($this->makeSubscription('non_existent'));
    }

    public function test_cancel_subscription(): void
    {
        $customer     = $this->createCustomer();
        $subscription = $this->subscriptionService->create($this->makeSubscription($customer->id));

        $this->subscriptionService->cancel($subscription->id);

        $cancelled = $this->subscriptionService->get($subscription->id);

        $this->assertSame(SubscriptionStatus::INACTIVE, $cancelled->status);
        $this->assertFalse($cancelled->isActive());
    }

    public function test_get_subscription_payments_returns_empty_list(): void
    {
        $customer     = $this->createCustomer();
        $subscription = $this->subscriptionService->create($this->makeSubscription($customer->id));

        $payments = $this->subscriptionService->payments($subscription->id);

        $this->assertIsArray($payments);
        $this->assertEmpty($payments);
    }

    public function test_cancel_throws_for_unknown_subscription(): void
    {
        $this->expectException(SubscriptionException::class);

        $this->subscriptionService->cancel('sub_inexistente');
    }

    public function test_get_subscription_throws_for_unknown_id(): void
    {
        $this->expectException(SubscriptionException::class);

        $this->subscriptionService->get('sub_inexistente');
    }
}
