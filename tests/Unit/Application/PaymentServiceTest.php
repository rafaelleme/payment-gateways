<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Application\Services\PaymentService;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\PaymentStatus;

class PaymentServiceTest extends TestCase
{
    private PaymentGateway $gateway;
    private PaymentService $service;

    protected function setUp(): void
    {
        $this->gateway = $this->createMock(PaymentGateway::class);
        $this->service = new PaymentService($this->gateway);
    }

    private function makePayment(?string $id = null, ?PaymentStatus $status = null): Payment
    {
        return new Payment(
            customerId:  new CustomerId('cus_abc123'),
            value:       new Money(150.00),
            billingType: BillingType::PIX,
            dueDate:     '2026-04-01',
            id:          $id,
            status:      $status,
        );
    }

    public function test_create_delegates_to_gateway(): void
    {
        $payment  = $this->makePayment();
        $returned = $this->makePayment('pay_001', PaymentStatus::PENDING);

        $this->gateway
            ->expects($this->once())
            ->method('createPayment')
            ->with($payment)
            ->willReturn($returned);

        $result = $this->service->create($payment);

        $this->assertSame($returned, $result);
        $this->assertSame('pay_001', $result->id);
        $this->assertTrue($result->isPersisted());
    }

    public function test_get_delegates_to_gateway(): void
    {
        $returned = $this->makePayment('pay_999', PaymentStatus::CONFIRMED);

        $this->gateway
            ->expects($this->once())
            ->method('getPayment')
            ->with('pay_999')
            ->willReturn($returned);

        $result = $this->service->get('pay_999');

        $this->assertSame($returned, $result);
        $this->assertTrue($result->isPaid());
    }
}
