<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\PaymentStatus;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;

class AsaasGatewayTest extends TestCase
{
    private function fakeAsaasResponse(): array
    {
        return [
            'id'          => 'pay_asaas_123',
            'customer'    => 'cus_abc',
            'billingType' => 'PIX',
            'value'       => 250.00,
            'dueDate'     => '2026-04-15',
            'status'      => 'PENDING',
            'invoiceUrl'  => 'https://asaas.com/invoice/abc',
        ];
    }

    private function makeGatewayWithMockedHttp(string $method, string $path, array $responseBody): AsaasGateway
    {
        $gateway = $this->getMockBuilder(AsaasGateway::class)
            ->setConstructorArgs(['fake-api-key', 'https://api.asaas.com/v3'])
            ->onlyMethods([])
            ->getMock();

        $httpMock = $this->createMock(Client::class);
        $response = new Response(200, [], json_encode($responseBody));

        $httpMock->expects($this->once())
            ->method($method)
            ->with($path, $this->anything())
            ->willReturn($response);

        $reflection = new \ReflectionProperty(AsaasGateway::class, 'http');
        $reflection->setValue($gateway, $httpMock);

        return $gateway;
    }

    public function test_create_payment_returns_payment_entity(): void
    {
        $gateway = $this->makeGatewayWithMockedHttp('post', '/payments', $this->fakeAsaasResponse());

        $payment = new Payment(
            customerId:  new CustomerId('cus_abc'),
            value:       new Money(250.00),
            billingType: BillingType::PIX,
            dueDate:     '2026-04-15',
        );

        $result = $gateway->createPayment($payment);

        $this->assertInstanceOf(Payment::class, $result);
        $this->assertSame('pay_asaas_123', $result->id);
        $this->assertSame(PaymentStatus::PENDING, $result->status);
        $this->assertSame(250.00, $result->value->getAmount());
        $this->assertSame(BillingType::PIX, $result->billingType);
        $this->assertTrue($result->isPersisted());
        $this->assertFalse($result->isPaid());
    }

    public function test_get_payment_returns_payment_entity(): void
    {
        $responseBody           = $this->fakeAsaasResponse();
        $responseBody['status'] = 'CONFIRMED';

        $httpMock = $this->createMock(Client::class);
        $response = new Response(200, [], json_encode($responseBody));

        $httpMock->expects($this->once())
            ->method('get')
            ->with('/payments/pay_asaas_123')
            ->willReturn($response);

        $gateway    = new AsaasGateway('fake-api-key');
        $reflection = new \ReflectionProperty(AsaasGateway::class, 'http');
        $reflection->setValue($gateway, $httpMock);

        $result = $gateway->getPayment('pay_asaas_123');

        $this->assertInstanceOf(Payment::class, $result);
        $this->assertSame('pay_asaas_123', $result->id);
        $this->assertSame(PaymentStatus::CONFIRMED, $result->status);
        $this->assertTrue($result->isPaid());
    }
}
