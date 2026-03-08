<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;

class CustomerIdTest extends TestCase
{
    public function test_creates_customer_id(): void
    {
        $id = new CustomerId('cus_123');

        $this->assertSame('cus_123', $id->getValue());
        $this->assertSame('cus_123', (string) $id);
    }

    public function test_throws_exception_for_empty_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CustomerId('   ');
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = new CustomerId('cus_abc');
        $b = new CustomerId('cus_abc');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_value(): void
    {
        $a = new CustomerId('cus_abc');
        $b = new CustomerId('cus_xyz');

        $this->assertFalse($a->equals($b));
    }
}
