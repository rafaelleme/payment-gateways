<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Laravel\Models\GatewaySubscription;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\SubscriptionPaymentFailed;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\SubscriptionPaymentReceived;

class UpdatePaymentStatusOnWebhook
{
    public function __construct(
        private readonly PaymentRepositoryContract $paymentRepository,
        private readonly SubscriptionRepositoryContract $subscriptionRepository,
        private readonly Dispatcher $events,
        private readonly string $gateway,
    ) {
    }

    public function handleReceived(PaymentReceived $event): void
    {
        $this->update($event->payment, PaymentStatus::RECEIVED);

        $subscriptionId = (string) ($event->payment['subscription'] ?? '');

        if ($subscriptionId !== '') {
            // Clear failed_at — payment recovered
            GatewaySubscription::where('gateway', $this->gateway)
                ->where('gateway_subscription_id', $subscriptionId)
                ->whereNotNull('failed_at')
                ->update(['failed_at' => null]);

            $this->events->dispatch(new SubscriptionPaymentReceived(
                payment: $event->payment,
                gateway: $this->gateway,
            ));
        }
    }

    public function handleOverdue(PaymentOverdue $event): void
    {
        $this->update($event->payment, PaymentStatus::OVERDUE);
        $this->markFailedAt($event->payment);

        $this->events->dispatch(new SubscriptionPaymentFailed(
            payment: $event->payment,
            gateway: $this->gateway,
            reason:  'OVERDUE',
        ));
    }

    public function handleRefused(PaymentRefused $event): void
    {
        $this->update($event->payment, PaymentStatus::FAILED);
        $this->markFailedAt($event->payment);

        $this->events->dispatch(new SubscriptionPaymentFailed(
            payment: $event->payment,
            gateway: $this->gateway,
            reason:  'REFUSED',
        ));
    }

    /** @param array<string, mixed> $payment */
    private function update(array $payment, PaymentStatus $status): void
    {
        $paymentId = (string) ($payment['id'] ?? '');

        if ($paymentId === '') {
            return;
        }

        $this->paymentRepository->upsertFromWebhook($this->gateway, $payment, $status->value);

        $subscriptionId = (string) ($payment['subscription'] ?? '');

        if ($subscriptionId !== '' && $status->isFailure()) {
            $this->subscriptionRepository->updateStatus($this->gateway, $subscriptionId, $status->value);
        }
    }

    /** @param array<string, mixed> $payment */
    private function markFailedAt(array $payment): void
    {
        $subscriptionId = (string) ($payment['subscription'] ?? '');

        if ($subscriptionId === '') {
            return;
        }

        // Only set failed_at once — don't overwrite the original failure timestamp
        GatewaySubscription::where('gateway', $this->gateway)
            ->where('gateway_subscription_id', $subscriptionId)
            ->whereNull('failed_at')
            ->update(['failed_at' => now()]);
    }
}
