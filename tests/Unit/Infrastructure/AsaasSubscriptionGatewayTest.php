<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasClient;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasSubscriptionGateway;

class AsaasSubscriptionGatewayTest extends TestCase
{
    private function fakeSubscriptionResponse(string $status = 'ACTIVE'): array
    {
        return [
            'id'          => 'sub_asaas_001',
            'customer'    => 'cus_abc',
            'billingType' => 'CREDIT_CARD',
            'value'       => 29.90,
            'cycle'       => 'MONTHLY',
            'nextDueDate' => '2026-04-01',
            'status'      => $status,
            'description' => 'Plano Pro',
        ];
    }

    private function makeSubscription(): Subscription
    {
        return new Subscription(
            customerId:  new CustomerId('cus_abc'),
            value:       new Money(29.90),
            billingType: BillingType::CREDIT_CARD,
            cycle:       SubscriptionCycle::MONTHLY,
            nextDueDate: '2026-04-01',
            description: 'Plano Pro',
        );
    }

    public function test_create_subscription_returns_subscription_entity(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('createSubscription')
            ->willReturn($this->fakeSubscriptionResponse());

        $result = (new AsaasSubscriptionGateway($client))->createSubscription($this->makeSubscription());

        $this->assertInstanceOf(Subscription::class, $result);
        $this->assertSame('sub_asaas_001', $result->id);
        $this->assertSame(SubscriptionStatus::ACTIVE, $result->status);
        $this->assertSame(29.90, $result->value->getAmount());
        $this->assertSame(SubscriptionCycle::MONTHLY, $result->cycle);
        $this->assertTrue($result->isPersisted());
        $this->assertTrue($result->isActive());
    }

    public function test_create_subscription_throws_on_api_error(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('createSubscription')->willReturn([
            'errors' => [['description' => 'Customer not found']],
        ]);

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Customer not found');

        (new AsaasSubscriptionGateway($client))->createSubscription($this->makeSubscription());
    }

    public function test_get_subscription_returns_subscription_entity(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('getSubscription')
            ->with('sub_asaas_001')
            ->willReturn($this->fakeSubscriptionResponse());

        $result = (new AsaasSubscriptionGateway($client))->getSubscription('sub_asaas_001');

        $this->assertSame('sub_asaas_001', $result->id);
        $this->assertSame(SubscriptionStatus::ACTIVE, $result->status);
    }

    public function test_get_subscription_throws_when_not_found(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('getSubscription')->willReturn([]);

        $this->expectException(SubscriptionException::class);

        (new AsaasSubscriptionGateway($client))->getSubscription('sub_missing');
    }

    public function test_cancel_subscription_calls_client(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('cancelSubscription')
            ->with('sub_asaas_001')
            ->willReturn([]);

        (new AsaasSubscriptionGateway($client))->cancelSubscription('sub_asaas_001');
    }

    public function test_cancel_subscription_throws_on_api_error(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('cancelSubscription')->willReturn([
            'errors' => [['description' => 'Subscription already cancelled']],
        ]);

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Subscription already cancelled');

        (new AsaasSubscriptionGateway($client))->cancelSubscription('sub_asaas_001');
    }

    public function test_get_subscription_payments_returns_payment_list(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('getSubscriptionPayments')->willReturn([
            'data' => [
                [
                    'id'          => 'pay_001',
                    'customer'    => 'cus_abc',
                    'billingType' => 'CREDIT_CARD',
                    'value'       => 29.90,
                    'dueDate'     => '2026-04-01',
                    'status'      => 'CONFIRMED',
                ],
            ],
        ]);

        $payments = (new AsaasSubscriptionGateway($client))->getSubscriptionPayments('sub_asaas_001');

        $this->assertCount(1, $payments);
        $this->assertSame('pay_001', $payments[0]->id);
    }
}
