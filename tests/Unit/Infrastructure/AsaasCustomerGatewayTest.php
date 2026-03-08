<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\CustomerException;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasClient;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;

class AsaasCustomerGatewayTest extends TestCase
{
    private function fakeCustomerResponse(): array
    {
        return [
            'id'    => 'cus_asaas_001',
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ];
    }

    private function makeCustomer(): Customer
    {
        return new Customer(
            name:  'John Doe',
            email: 'john@example.com',
        );
    }

    public function test_create_customer_returns_customer_entity(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('createCustomer')
            ->willReturn($this->fakeCustomerResponse());

        $result = (new AsaasGateway($client))->createCustomer($this->makeCustomer());

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertSame('cus_asaas_001', $result->id);
        $this->assertSame('John Doe', $result->name);
        $this->assertTrue($result->isPersisted());
    }

    public function test_create_customer_throws_on_api_error(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('createCustomer')->willReturn([
            'errors' => [['description' => 'CPF/CNPJ already registered']],
        ]);

        $this->expectException(CustomerException::class);
        $this->expectExceptionMessage('CPF/CNPJ already registered');

        (new AsaasGateway($client))->createCustomer($this->makeCustomer());
    }

    public function test_get_customer_returns_customer_entity(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('getCustomer')
            ->with('cus_asaas_001')
            ->willReturn($this->fakeCustomerResponse());

        $result = (new AsaasGateway($client))->getCustomer('cus_asaas_001');

        $this->assertSame('cus_asaas_001', $result->id);
        $this->assertSame('john@example.com', $result->email);
    }

    public function test_get_customer_throws_when_not_found(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('getCustomer')->willReturn([]);

        $this->expectException(CustomerException::class);
        $this->expectExceptionMessage('Customer [cus_missing] not found.');

        (new AsaasGateway($client))->getCustomer('cus_missing');
    }
}
