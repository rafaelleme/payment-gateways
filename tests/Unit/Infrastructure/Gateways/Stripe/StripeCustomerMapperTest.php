<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure\Gateways\Stripe;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Customers\StripeCustomerMapper;

class StripeCustomerMapperTest extends TestCase
{
    private StripeCustomerMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new StripeCustomerMapper();
    }

    public function testToCustomerMapsStripeDataCorrectly(): void
    {
        $data = [
            'id'       => 'cus_1234567890',
            'name'     => 'João Silva',
            'email'    => 'joao@example.com',
            'phone'    => '11999999999',
            'metadata' => [
                'cpfCnpj'           => '12345678901234',
                'externalReference' => 456,
            ],
        ];

        $customer = $this->mapper->toCustomer($data);

        $this->assertEquals('cus_1234567890', $customer->id);
        $this->assertEquals('João Silva', $customer->name);
        $this->assertEquals('joao@example.com', $customer->email);
        $this->assertEquals('11999999999', $customer->phone);
        $this->assertEquals('12345678901234', $customer->cpfCnpj);
        $this->assertEquals(456, $customer->externalReference);
    }

    public function testToCustomerHandlesOptionalFields(): void
    {
        $data = [
            'id'       => 'cus_test',
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'metadata' => [],
        ];

        $customer = $this->mapper->toCustomer($data);

        $this->assertEquals('cus_test', $customer->id);
        $this->assertEquals('Test User', $customer->name);
        $this->assertNull($customer->phone);
        $this->assertNull($customer->cpfCnpj);
        $this->assertNull($customer->externalReference);
    }
}
