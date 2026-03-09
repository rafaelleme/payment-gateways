<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\Subscriptions;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;

class AsaasSubscriptionMapper
{
    /** @param array<string, mixed> $data */
    public function toSubscription(array $data): Subscription
    {
        return new Subscription(
            customerId:        new CustomerId((string) ($data['customer'] ?? '')),
            value:             new Money((float) ($data['value'] ?? 0)),
            billingType:       BillingType::fromAsaas((string) ($data['billingType'] ?? '')),
            cycle:             SubscriptionCycle::fromAsaas((string) ($data['cycle'] ?? '')),
            nextDueDate:       (string) ($data['nextDueDate'] ?? ''),
            description:       isset($data['description']) ? (string) $data['description'] : null,
            externalReference: isset($data['externalReference']) ? (int) $data['externalReference'] : null,
            id:                (string) $data['id'],
            status:            SubscriptionStatus::fromAsaas((string) ($data['status'] ?? '')),
        );
    }
}
