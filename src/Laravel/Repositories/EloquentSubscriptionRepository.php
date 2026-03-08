<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Repositories;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Laravel\Models\GatewaySubscription;

class EloquentSubscriptionRepository implements SubscriptionRepositoryContract
{
    public function save(string $gateway, Subscription $subscription, ?int $userId = null, ?int $localCustomerId = null): void
    {
        GatewaySubscription::updateOrCreate(
            [
                'gateway'                 => $gateway,
                'gateway_subscription_id' => $subscription->id,
            ],
            [
                'user_id'       => $userId,
                'customer_id'   => $localCustomerId,
                'status'        => $subscription->status?->value ?? 'ACTIVE',
                'billing_type'  => $subscription->billingType->value,
                'value'         => $subscription->value->getAmount(),
                'next_due_date' => $subscription->nextDueDate,
            ],
        );
    }

    public function updateStatus(string $gateway, string $gatewaySubscriptionId, string $status): void
    {
        GatewaySubscription::where('gateway', $gateway)
            ->where('gateway_subscription_id', $gatewaySubscriptionId)
            ->update(['status' => $status]);
    }

    public function findByGatewayId(string $gateway, string $gatewaySubscriptionId): ?Subscription
    {
        $record = GatewaySubscription::where('gateway', $gateway)
            ->where('gateway_subscription_id', $gatewaySubscriptionId)
            ->first();

        if ($record === null) {
            return null;
        }

        return new Subscription(
            customerId:  new CustomerId((string) $record->customer?->gateway_customer_id ?? ''),
            value:       new Money((float) $record->value),
            billingType: BillingType::from($record->billing_type),
            cycle:       SubscriptionCycle::MONTHLY,
            nextDueDate: $record->next_due_date?->format('Y-m-d') ?? '',
            id:          $record->gateway_subscription_id,
            status:      SubscriptionStatus::fromAsaas($record->status),
        );
    }

    public function findLocalId(string $gateway, string $gatewaySubscriptionId): ?int
    {
        $record = GatewaySubscription::where('gateway', $gateway)
            ->where('gateway_subscription_id', $gatewaySubscriptionId)
            ->first();

        return $record?->id;
    }
}
