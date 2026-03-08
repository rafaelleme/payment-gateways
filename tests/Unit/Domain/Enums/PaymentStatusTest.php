<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\Enums;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;

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
        $this->assertSame('FAILED', PaymentStatus::FAILED->value);
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
        $this->assertFalse(PaymentStatus::FAILED->isPaid());
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Aguardando Pagamento', PaymentStatus::PENDING->label());
        $this->assertSame('Confirmado', PaymentStatus::CONFIRMED->label());
        $this->assertSame('Falhou', PaymentStatus::FAILED->label());
    }

    public function test_from_asaas_maps_known_values(): void
    {
        $this->assertSame(PaymentStatus::CONFIRMED, PaymentStatus::fromAsaas('CONFIRMED'));
        $this->assertSame(PaymentStatus::RECEIVED, PaymentStatus::fromAsaas('RECEIVED'));
        $this->assertSame(PaymentStatus::RECEIVED, PaymentStatus::fromAsaas('DUNNING_RECEIVED'));
        $this->assertSame(PaymentStatus::OVERDUE, PaymentStatus::fromAsaas('OVERDUE'));
        $this->assertSame(PaymentStatus::OVERDUE, PaymentStatus::fromAsaas('DUNNING_REQUESTED'));
        $this->assertSame(PaymentStatus::REFUNDED, PaymentStatus::fromAsaas('REFUNDED'));
        $this->assertSame(PaymentStatus::REFUNDED, PaymentStatus::fromAsaas('REFUND_IN_PROGRESS'));
        $this->assertSame(PaymentStatus::CANCELLED, PaymentStatus::fromAsaas('CHARGEBACK_REQUESTED'));
        $this->assertSame(PaymentStatus::CANCELLED, PaymentStatus::fromAsaas('CHARGEBACK_DISPUTE'));
    }

    public function test_from_asaas_returns_pending_for_unknown_value(): void
    {
        $this->assertSame(PaymentStatus::PENDING, PaymentStatus::fromAsaas('PENDING'));
        $this->assertSame(PaymentStatus::PENDING, PaymentStatus::fromAsaas('AWAITING_RISK_ANALYSIS'));
        $this->assertSame(PaymentStatus::PENDING, PaymentStatus::fromAsaas(''));
        $this->assertSame(PaymentStatus::PENDING, PaymentStatus::fromAsaas('UNKNOWN'));
    }
}
