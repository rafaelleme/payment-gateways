<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Laravel\Models\GatewaySubscription;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\SubscriptionPaymentFailed;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\SubscriptionPaymentReceived;

class UpdateAsaasPaymentStatusOnWebhook
{
    private const GATEWAY = 'asaas';

    public function __construct(
        private readonly PaymentRepositoryContract $paymentRepository,
        private readonly SubscriptionRepositoryContract $subscriptionRepository,
        private readonly Dispatcher $events,
    ) {
    }

    public function handleReceived(PaymentReceived $event): void
    {
        $this->update($event->payment, PaymentStatus::RECEIVED);

        $subscriptionId = (string) ($event->payment['subscription'] ?? '');

        if ($subscriptionId !== '') {
            // Clear failed_at — payment recovered
            GatewaySubscription::where('gateway', self::GATEWAY)
                ->where('gateway_subscription_id', $subscriptionId)
                ->whereNotNull('failed_at')
                ->update(['failed_at' => null]);

            $this->events->dispatch(new SubscriptionPaymentReceived(
                payment: $event->payment,
                gateway: self::GATEWAY,
            ));
        }
    }

    public function handleOverdue(PaymentOverdue $event): void
    {
        $this->update($event->payment, PaymentStatus::OVERDUE);
        $this->markFailedAt($event->payment);

        $this->events->dispatch(new SubscriptionPaymentFailed(
            payment: $event->payment,
            gateway: self::GATEWAY,
            reason:  'OVERDUE',
        ));
    }

    public function handleRefused(PaymentRefused $event): void
    {
        $this->update($event->payment, PaymentStatus::FAILED);
        $this->markFailedAt($event->payment);

        $this->events->dispatch(new SubscriptionPaymentFailed(
            payment: $event->payment,
            gateway: self::GATEWAY,
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

        $this->paymentRepository->upsertFromWebhook(self::GATEWAY, $payment, $status->value);

        $subscriptionId = (string) ($payment['subscription'] ?? '');

        if ($subscriptionId !== '') {
            $subscriptionStatus = $this->mapPaymentStatusToSubscriptionStatus($status);
            $this->subscriptionRepository->updateStatus(self::GATEWAY, $subscriptionId, $subscriptionStatus->value);
        }
    }

    /**
     * Maps PaymentStatus to SubscriptionStatus.
     * - RECEIVED or CONFIRMED → ACTIVE (payment successful)
     * - FAILED, OVERDUE → INACTIVE (payment failed or overdue)
     * - Other → INACTIVE (conservative default)
     */
    private function mapPaymentStatusToSubscriptionStatus(PaymentStatus $status): SubscriptionStatus
    {
        return match ($status) {
            PaymentStatus::RECEIVED,
            PaymentStatus::CONFIRMED => SubscriptionStatus::ACTIVE,
            PaymentStatus::FAILED,
            PaymentStatus::OVERDUE => SubscriptionStatus::INACTIVE,
            default                => SubscriptionStatus::INACTIVE,
        };
    }

    /** @param array<string, mixed> $payment */
    private function markFailedAt(array $payment): void
    {
        $subscriptionId = (string) ($payment['subscription'] ?? '');

        if ($subscriptionId === '') {
            return;
        }

        // Only set failed_at once — don't overwrite the original failure timestamp
        GatewaySubscription::where('gateway', self::GATEWAY)
            ->where('gateway_subscription_id', $subscriptionId)
            ->whereNull('failed_at')
            ->update(['failed_at' => now()]);
    }
}
