<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\PaymentStatus;

class PaymentStatusTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $this->assertSame('PENDING', PaymentStatus::PENDING->value);
        $this->assertSame('CONFIRMED', PaymentStatus::CONFIRMED->value);
        $this->assertSame('RECEIVED', PaymentStatus::RECEIVED->value);
        $this->assertSame('OVERDUE', PaymentStatus::OVERDUE->value);
        $this->assertSame('REFUNDED', PaymentStatus::REFUNDED->value);
        $this->assertSame('CANCELLED', PaymentStatus::CANCELLED->value);
    }

    public function test_is_paid_returns_true_for_paid_statuses(): void
    {
        $this->assertTrue(PaymentStatus::CONFIRMED->isPaid());
        $this->assertTrue(PaymentStatus::RECEIVED->isPaid());
    }

    public function test_is_paid_returns_false_for_unpaid_statuses(): void
    {
        $this->assertFalse(PaymentStatus::PENDING->isPaid());
        $this->assertFalse(PaymentStatus::OVERDUE->isPaid());
        $this->assertFalse(PaymentStatus::CANCELLED->isPaid());
        $this->assertFalse(PaymentStatus::REFUNDED->isPaid());
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Aguardando Pagamento', PaymentStatus::PENDING->label());
        $this->assertSame('Confirmado', PaymentStatus::CONFIRMED->label());
    }
}
