<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\PaymentException;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasClient;
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

    private function makePayment(): Payment
    {
        return new Payment(
            customerId:  new CustomerId('cus_abc'),
            value:       new Money(250.00),
            billingType: BillingType::PIX,
            dueDate:     '2026-04-15',
        );
    }

    public function test_create_payment_returns_payment_entity(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('createPayment')
            ->willReturn($this->fakeAsaasResponse());

        $result = (new AsaasGateway($client))->createPayment($this->makePayment());

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
        $response           = $this->fakeAsaasResponse();
        $response['status'] = 'CONFIRMED';

        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('getPayment')
            ->with('pay_asaas_123')
            ->willReturn($response);

        $result = (new AsaasGateway($client))->getPayment('pay_asaas_123');

        $this->assertInstanceOf(Payment::class, $result);
        $this->assertSame('pay_asaas_123', $result->id);
        $this->assertSame(PaymentStatus::CONFIRMED, $result->status);
        $this->assertTrue($result->isPaid());
    }

    public function test_pix_fields_are_mapped_when_present(): void
    {
        $response              = $this->fakeAsaasResponse();
        $response['pixQrCode'] = 'qr_code_base64';
        $response['pixKey']    = 'chave@pix.com';

        $client = $this->createMock(AsaasClient::class);
        $client->method('createPayment')->willReturn($response);

        $result = (new AsaasGateway($client))->createPayment($this->makePayment());

        $this->assertSame('qr_code_base64', $result->pixQrCode);
        $this->assertSame('chave@pix.com', $result->pixKey);
    }

    public function test_create_payment_throws_on_api_error(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('createPayment')->willReturn([
            'errors' => [['description' => 'Customer not found']],
        ]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Customer not found');

        (new AsaasGateway($client))->createPayment($this->makePayment());
    }

    public function test_get_payment_throws_when_not_found(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('getPayment')->willReturn([]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Payment [pay_missing] not found.');

        (new AsaasGateway($client))->getPayment('pay_missing');
    }
}
