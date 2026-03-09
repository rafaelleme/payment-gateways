<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasWebhookEvent;

class AsaasWebhookEventTest extends TestCase
{
    public function test_to_dispatch_event_returns_received_for_payment_events(): void
    {
        $this->assertSame('received', AsaasWebhookEvent::PAYMENT_RECEIVED->toDispatchEvent());
        $this->assertSame('received', AsaasWebhookEvent::PAYMENT_CONFIRMED->toDispatchEvent());
        $this->assertSame('received', AsaasWebhookEvent::PAYMENT_DUNNING_RECEIVED->toDispatchEvent());
    }

    public function test_to_dispatch_event_returns_overdue_for_overdue_events(): void
    {
        $this->assertSame('overdue', AsaasWebhookEvent::PAYMENT_OVERDUE->toDispatchEvent());
        $this->assertSame('overdue', AsaasWebhookEvent::PAYMENT_DUNNING_REQUESTED->toDispatchEvent());
    }

    public function test_to_dispatch_event_returns_refused_for_chargeback_events(): void
    {
        $this->assertSame('refused', AsaasWebhookEvent::PAYMENT_CHARGEBACK_REQUESTED->toDispatchEvent());
        $this->assertSame('refused', AsaasWebhookEvent::PAYMENT_CHARGEBACK_DISPUTE->toDispatchEvent());
        $this->assertSame('refused', AsaasWebhookEvent::PAYMENT_AWAITING_CHARGEBACK_REVERSAL->toDispatchEvent());
        $this->assertSame('refused', AsaasWebhookEvent::PAYMENT_REPROVED_BY_RISK_ANALYSIS->toDispatchEvent());
    }

    public function test_to_dispatch_event_returns_null_for_non_dispatchable_events(): void
    {
        $this->assertNull(AsaasWebhookEvent::PAYMENT_CREATED->toDispatchEvent());
        $this->assertNull(AsaasWebhookEvent::PAYMENT_BANK_SLIP_VIEWED->toDispatchEvent());
        $this->assertNull(AsaasWebhookEvent::SUBSCRIPTION_CREATED->toDispatchEvent());
        $this->assertNull(AsaasWebhookEvent::TRANSFER_DONE->toDispatchEvent());
        $this->assertNull(AsaasWebhookEvent::INVOICE_PAID->toDispatchEvent());
    }

    public function test_to_payment_status_maps_correctly(): void
    {
        $this->assertSame(PaymentStatus::RECEIVED, AsaasWebhookEvent::PAYMENT_RECEIVED->toPaymentStatus());
        $this->assertSame(PaymentStatus::RECEIVED, AsaasWebhookEvent::PAYMENT_DUNNING_RECEIVED->toPaymentStatus());
        $this->assertSame(PaymentStatus::CONFIRMED, AsaasWebhookEvent::PAYMENT_CONFIRMED->toPaymentStatus());
        $this->assertSame(PaymentStatus::CONFIRMED, AsaasWebhookEvent::PAYMENT_AUTHORIZED->toPaymentStatus());
        $this->assertSame(PaymentStatus::OVERDUE, AsaasWebhookEvent::PAYMENT_OVERDUE->toPaymentStatus());
        $this->assertSame(PaymentStatus::OVERDUE, AsaasWebhookEvent::PAYMENT_DUNNING_REQUESTED->toPaymentStatus());
        $this->assertSame(PaymentStatus::REFUNDED, AsaasWebhookEvent::PAYMENT_REFUNDED->toPaymentStatus());
        $this->assertSame(PaymentStatus::REFUNDED, AsaasWebhookEvent::PAYMENT_PARTIALLY_REFUNDED->toPaymentStatus());
        $this->assertSame(PaymentStatus::CANCELLED, AsaasWebhookEvent::PAYMENT_CHARGEBACK_REQUESTED->toPaymentStatus());
        $this->assertSame(PaymentStatus::FAILED, AsaasWebhookEvent::PAYMENT_REPROVED_BY_RISK_ANALYSIS->toPaymentStatus());
        $this->assertSame(PaymentStatus::PENDING, AsaasWebhookEvent::PAYMENT_CREATED->toPaymentStatus());
    }

    public function test_to_payment_status_returns_null_for_non_payment_events(): void
    {
        $this->assertNull(AsaasWebhookEvent::SUBSCRIPTION_CREATED->toPaymentStatus());
        $this->assertNull(AsaasWebhookEvent::TRANSFER_DONE->toPaymentStatus());
        $this->assertNull(AsaasWebhookEvent::INVOICE_PAID->toPaymentStatus());
        $this->assertNull(AsaasWebhookEvent::BILL_DONE->toPaymentStatus());
    }

    public function test_try_from_returns_null_for_unknown_event(): void
    {
        $this->assertNull(AsaasWebhookEvent::tryFrom('PAYMENT_REFUSED'));
        $this->assertNull(AsaasWebhookEvent::tryFrom('UNKNOWN_EVENT'));
        $this->assertNull(AsaasWebhookEvent::tryFrom(''));
    }
}
