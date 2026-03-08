<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;

class BillingTypeTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $this->assertSame('BOLETO', BillingType::BOLETO->value);
        $this->assertSame('PIX', BillingType::PIX->value);
        $this->assertSame('CREDIT_CARD', BillingType::CREDIT_CARD->value);
        $this->assertSame('DEBIT_CARD', BillingType::DEBIT_CARD->value);
        $this->assertSame('TRANSFER', BillingType::TRANSFER->value);
        $this->assertSame('UNDEFINED', BillingType::UNDEFINED->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('PIX', BillingType::PIX->label());
        $this->assertSame('Boleto Bancário', BillingType::BOLETO->label());
        $this->assertSame('Cartão de Crédito', BillingType::CREDIT_CARD->label());
    }

    public function test_can_be_created_from_value(): void
    {
        $type = BillingType::from('PIX');

        $this->assertSame(BillingType::PIX, $type);
    }
}
