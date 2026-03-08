<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\PaymentStatus;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\FakeGateway;

class FakeGatewayTest extends TestCase
{
    private FakeGateway $gateway;

    protected function setUp(): void
    {
        $this->gateway = new FakeGateway();
    }

    private function makePayment(): Payment
    {
        return new Payment(
            customerId:  new CustomerId('cus_test'),
            value:       new Money(99.90),
            billingType: BillingType::BOLETO,
            dueDate:     '2026-05-01',
        );
    }

    public function test_create_returns_persisted_payment_with_pending_status(): void
    {
        $result = $this->gateway->createPayment($this->makePayment());

        $this->assertInstanceOf(Payment::class, $result);
        $this->assertTrue($result->isPersisted());
        $this->assertSame(PaymentStatus::PENDING, $result->status);
        $this->assertStringStartsWith('fake_pay_', $result->id);
    }

    public function test_create_preserves_original_payment_data(): void
    {
        $payment = $this->makePayment();
        $result  = $this->gateway->createPayment($payment);

        $this->assertSame($payment->customerId->getValue(), $result->customerId->getValue());
        $this->assertSame($payment->value->getAmount(), $result->value->getAmount());
        $this->assertSame($payment->billingType, $result->billingType);
        $this->assertSame($payment->dueDate, $result->dueDate);
    }

    public function test_get_returns_previously_created_payment(): void
    {
        $created = $this->gateway->createPayment($this->makePayment());
        $fetched = $this->gateway->getPayment($created->id);

        $this->assertSame($created->id, $fetched->id);
    }

    public function test_get_throws_exception_for_unknown_payment(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->gateway->getPayment('non_existent');
    }

    public function test_each_creation_generates_sequential_ids(): void
    {
        $first  = $this->gateway->createPayment($this->makePayment());
        $second = $this->gateway->createPayment($this->makePayment());

        $this->assertSame('fake_pay_1', $first->id);
        $this->assertSame('fake_pay_2', $second->id);
    }

    public function test_reset_clears_all_payments(): void
    {
        $this->gateway->createPayment($this->makePayment());
        $this->gateway->reset();

        $this->assertEmpty($this->gateway->allPayments());

        $fresh = $this->gateway->createPayment($this->makePayment());
        $this->assertSame('fake_pay_1', $fresh->id);
    }
}
