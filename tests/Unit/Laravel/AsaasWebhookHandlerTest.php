<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Laravel;

use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\AsaasWebhookHandler;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

class AsaasWebhookHandlerTest extends TestCase
{
    /** @param array<string, mixed> $payment */
    private function payload(string $event, array $payment = []): array
    {
        return [
            'event'   => $event,
            'payment' => $payment ?: [
                'id'           => 'pay_xxx',
                'subscription' => 'sub_xxx',
                'value'        => 49.90,
                'status'       => 'RECEIVED',
            ],
        ];
    }

    public function test_payment_received_dispatches_payment_received_event(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PaymentReceived::class));

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('PAYMENT_RECEIVED'));
    }

    public function test_payment_confirmed_dispatches_payment_received_event(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PaymentReceived::class));

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('PAYMENT_CONFIRMED'));
    }

    public function test_payment_overdue_dispatches_payment_overdue_event(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PaymentOverdue::class));

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('PAYMENT_OVERDUE'));
    }

    public function test_payment_dunning_requested_dispatches_payment_overdue_event(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PaymentOverdue::class));

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('PAYMENT_DUNNING_REQUESTED'));
    }

    public function test_payment_chargeback_requested_dispatches_payment_refused_event(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PaymentRefused::class));

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('PAYMENT_CHARGEBACK_REQUESTED'));
    }

    public function test_unknown_event_dispatches_nothing(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->never())->method('dispatch');

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('UNKNOWN_EVENT'));
    }

    public function test_payment_refused_string_is_unknown_and_dispatches_nothing(): void
    {
        // 'PAYMENT_REFUSED' is not a real Asaas event — should be silently ignored
        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->never())->method('dispatch');

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('PAYMENT_REFUSED'));
    }

    public function test_event_carries_payment_data(): void
    {
        $payment    = ['id' => 'pay_abc', 'value' => 29.90, 'status' => 'RECEIVED'];
        $dispatched = null;

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->method('dispatch')->willReturnCallback(function ($event) use (&$dispatched) {
            $dispatched = $event;
        });

        (new AsaasWebhookHandler($dispatcher))->handle($this->payload('PAYMENT_RECEIVED', $payment));

        $this->assertInstanceOf(PaymentReceived::class, $dispatched);
        $this->assertSame('pay_abc', $dispatched->payment['id']);
        $this->assertSame(29.90, $dispatched->payment['value']);
    }
}
