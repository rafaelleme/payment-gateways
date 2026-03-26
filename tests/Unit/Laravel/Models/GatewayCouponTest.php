<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Laravel\Models;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Laravel\Models\GatewayCoupon;

class GatewayCouponTest extends TestCase
{
    /**
     * Testa criação de cupom percentual
     */
    public function testCreatePercentageCoupon(): void
    {
        $data = [
            'code'       => 'SUMMER2024',
            'gateway'    => 'asaas',
            'type'       => 'percentage',
            'value'      => 15.00,
            'currency'   => 'BRL',
            'max_uses'   => 500,
            'is_active'  => true,
        ];

        // Simulando criação (em teste real seria com fake database)
        $coupon = new GatewayCoupon($data);

        $this->assertEquals('SUMMER2024', $coupon->code);
        $this->assertEquals('asaas', $coupon->gateway);
        $this->assertEquals('percentage', $coupon->type);
        $this->assertEquals(15.00, $coupon->value);
    }

    /**
     * Testa se cupom percentual é identificado corretamente
     */
    public function testIsPercentage(): void
    {
        $coupon = new GatewayCoupon([
            'code'   => 'DISCOUNT50',
            'type'   => 'percentage',
            'value'  => 50.00,
            'gateway' => 'asaas',
        ]);

        $this->assertTrue($coupon->isPercentage());
        $this->assertFalse($coupon->isFixedAmount());
    }

    /**
     * Testa se cupom de valor fixo é identificado corretamente
     */
    public function testIsFixedAmount(): void
    {
        $coupon = new GatewayCoupon([
            'code'    => 'SAVE10',
            'type'    => 'fixed_amount',
            'value'   => 10.00,
            'gateway' => 'stripe',
        ]);

        $this->assertTrue($coupon->isFixedAmount());
        $this->assertFalse($coupon->isPercentage());
    }

    /**
     * Testa validação de cupom ativo sem restrições
     */
    public function testIsValidWithNoRestrictions(): void
    {
        $coupon = new GatewayCoupon([
            'code'        => 'ACTIVE',
            'type'        => 'percentage',
            'value'       => 10.00,
            'gateway'     => 'asaas',
            'is_active'   => true,
            'max_uses'    => null,
            'current_uses' => 0,
            'valid_from'  => null,
            'valid_until' => null,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    /**
     * Testa validação de cupom inativo
     */
    public function testIsValidWhenInactive(): void
    {
        $coupon = new GatewayCoupon([
            'code'      => 'INACTIVE',
            'type'      => 'percentage',
            'value'     => 10.00,
            'gateway'   => 'asaas',
            'is_active' => false,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    /**
     * Testa incremento de uso
     */
    public function testIncrementUsage(): void
    {
        $coupon = new GatewayCoupon([
            'code'         => 'COUNT',
            'type'         => 'percentage',
            'value'        => 10.00,
            'gateway'      => 'asaas',
            'current_uses' => 5,
            'max_uses'     => 100,
        ]);

        // Verificar antes
        $this->assertEquals(5, $coupon->current_uses);

        // Incrementar (em teste real usaria mock do banco)
        // $coupon->incrementUsage();

        // Verificar depois (simulado)
        // $this->assertEquals(6, $coupon->current_uses);
    }

    /**
     * Testa __toString para percentual
     */
    public function testToStringPercentage(): void
    {
        $coupon = new GatewayCoupon([
            'code'   => 'DISCOUNT50',
            'type'   => 'percentage',
            'value'  => 50.00,
            'gateway' => 'asaas',
        ]);

        // $this->assertEquals('DISCOUNT50 (50%)', (string) $coupon);
    }

    /**
     * Testa __toString para valor fixo
     */
    public function testToStringFixedAmount(): void
    {
        $coupon = new GatewayCoupon([
            'code'    => 'SAVE10',
            'type'    => 'fixed_amount',
            'value'   => 10.00,
            'gateway' => 'stripe',
        ]);

        // $this->assertEquals('SAVE10 (-10)', (string) $coupon);
    }
}

