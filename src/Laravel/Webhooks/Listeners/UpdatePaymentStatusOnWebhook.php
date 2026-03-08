<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks\Listeners;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

class UpdatePaymentStatusOnWebhook
{
    public function __construct(
        private readonly PaymentRepositoryContract $paymentRepository,
        private readonly SubscriptionRepositoryContract $subscriptionRepository,
        private readonly string $gateway,
    ) {
    }

    public function handleReceived(PaymentReceived $event): void
    {
        $this->update($event->payment, 'RECEIVED');
    }

    public function handleOverdue(PaymentOverdue $event): void
    {
        $this->update($event->payment, 'OVERDUE');
    }

    public function handleRefused(PaymentRefused $event): void
    {
        $this->update($event->payment, 'REFUSED');
    }

    /** @param array<string, mixed> $payment */
    private function update(array $payment, string $status): void
    {
        $paymentId = (string) ($payment['id'] ?? '');

        if ($paymentId === '') {
            return;
        }

        $this->paymentRepository->updateStatus($this->gateway, $paymentId, $status);

        $subscriptionId = (string) ($payment['subscription'] ?? '');

        if ($subscriptionId !== '' && in_array($status, ['OVERDUE', 'REFUSED'], true)) {
            $this->subscriptionRepository->updateStatus($this->gateway, $subscriptionId, $status);
        }
    }
}
