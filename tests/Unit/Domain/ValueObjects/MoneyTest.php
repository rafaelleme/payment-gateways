<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;

class MoneyTest extends TestCase
{
    public function test_creates_money_with_default_currency(): void
    {
        $money = new Money(100.50);

        $this->assertSame(100.50, $money->getAmount());
        $this->assertSame('BRL', $money->getCurrency());
    }

    public function test_creates_money_with_custom_currency(): void
    {
        $money = new Money(50.00, 'USD');

        $this->assertSame('USD', $money->getCurrency());
    }

    public function test_throws_exception_for_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(-1.00);
    }

    public function test_equals_returns_true_for_same_values(): void
    {
        $a = new Money(100.00);
        $b = new Money(100.00);

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_amounts(): void
    {
        $a = new Money(100.00);
        $b = new Money(200.00);

        $this->assertFalse($a->equals($b));
    }

    public function test_to_string_formats_correctly(): void
    {
        $money = new Money(99.9, 'BRL');

        $this->assertSame('99.90 BRL', (string) $money);
    }
}
