<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\Enums;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;

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
        $this->assertSame(BillingType::PIX, BillingType::from('PIX'));
    }

    public function test_from_asaas_maps_known_values(): void
    {
        $this->assertSame(BillingType::BOLETO, BillingType::fromAsaas('BOLETO'));
        $this->assertSame(BillingType::PIX, BillingType::fromAsaas('PIX'));
        $this->assertSame(BillingType::CREDIT_CARD, BillingType::fromAsaas('CREDIT_CARD'));
        $this->assertSame(BillingType::DEBIT_CARD, BillingType::fromAsaas('DEBIT_CARD'));
        $this->assertSame(BillingType::TRANSFER, BillingType::fromAsaas('TRANSFER'));
    }

    public function test_from_asaas_returns_undefined_for_unknown_value(): void
    {
        $this->assertSame(BillingType::UNDEFINED, BillingType::fromAsaas('UNKNOWN'));
        $this->assertSame(BillingType::UNDEFINED, BillingType::fromAsaas(''));
    }
}
