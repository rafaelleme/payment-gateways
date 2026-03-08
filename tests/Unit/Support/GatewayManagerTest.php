<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Support;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Support\GatewayManager;

class GatewayManagerTest extends TestCase
{
    public function test_registers_and_resolves_driver(): void
    {
        $mock    = $this->createMock(PaymentGateway::class);
        $manager = new GatewayManager('asaas');

        $manager->register('asaas', fn () => $mock);

        $this->assertSame($mock, $manager->driver('asaas'));
    }

    public function test_resolves_default_driver(): void
    {
        $mock    = $this->createMock(PaymentGateway::class);
        $manager = new GatewayManager('asaas');
        $manager->register('asaas', fn () => $mock);

        $this->assertSame($mock, $manager->driver());
    }

    public function test_driver_is_resolved_as_singleton(): void
    {
        $manager = new GatewayManager('asaas');
        $calls   = 0;

        $manager->register('asaas', function () use (&$calls) {
            $calls++;
            return $this->createMock(PaymentGateway::class);
        });

        $manager->driver('asaas');
        $manager->driver('asaas');

        $this->assertSame(1, $calls);
    }

    public function test_throws_exception_for_unregistered_driver(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $manager = new GatewayManager('stripe');
        $manager->driver('stripe');
    }

    public function test_throws_exception_when_no_default_set(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $manager = new GatewayManager();
        $manager->driver();
    }

    public function test_extend_is_alias_for_register(): void
    {
        $mock    = $this->createMock(PaymentGateway::class);
        $manager = new GatewayManager('custom');
        $manager->extend('custom', fn () => $mock);

        $this->assertSame($mock, $manager->driver('custom'));
    }

    public function test_has_returns_correct_boolean(): void
    {
        $manager = new GatewayManager();
        $manager->register('asaas', fn () => $this->createMock(PaymentGateway::class));

        $this->assertTrue($manager->has('asaas'));
        $this->assertFalse($manager->has('stripe'));
    }
}
