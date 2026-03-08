<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Application\Services\PaymentService;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\FakeGateway;

class PaymentServiceTest extends TestCase
{
    private FakeGateway $gateway;
    private PaymentService $service;

    protected function setUp(): void
    {
        $this->gateway = new FakeGateway();
        $this->service = new PaymentService($this->gateway);
    }

    private function makePayment(): Payment
    {
        return new Payment(
            customerId:  new CustomerId('cus_abc123'),
            value:       new Money(150.00),
            billingType: BillingType::PIX,
            dueDate:     '2026-04-01',
        );
    }

    public function test_create_returns_persisted_payment(): void
    {
        $result = $this->service->create($this->makePayment());

        $this->assertInstanceOf(Payment::class, $result);
        $this->assertTrue($result->isPersisted());
        $this->assertSame(PaymentStatus::PENDING, $result->status);
        $this->assertSame(150.00, $result->value->getAmount());
    }

    public function test_create_stores_payment_in_gateway(): void
    {
        $result = $this->service->create($this->makePayment());

        $this->assertTrue($this->gateway->hasPayment($result->id));
    }

    public function test_get_returns_previously_created_payment(): void
    {
        $created = $this->service->create($this->makePayment());
        $fetched = $this->service->get($created->id);

        $this->assertSame($created->id, $fetched->id);
        $this->assertSame($created->value->getAmount(), $fetched->value->getAmount());
    }

    public function test_each_creation_generates_unique_id(): void
    {
        $first  = $this->service->create($this->makePayment());
        $second = $this->service->create($this->makePayment());

        $this->assertNotSame($first->id, $second->id);
    }
}
