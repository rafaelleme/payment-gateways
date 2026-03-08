<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Entities;

use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;

readonly class Subscription
{
    public function __construct(
        public CustomerId          $customerId,
        public Money               $value,
        public BillingType         $billingType,
        public SubscriptionCycle   $cycle,
        public string              $nextDueDate,
        public ?string             $description = null,
        public ?string             $externalReference = null,
        public ?string             $id = null,
        public ?SubscriptionStatus $status = null,
    ) {
    }

    public function isPersisted(): bool
    {
        return $this->id !== null;
    }

    public function isActive(): bool
    {
        return $this->status?->isActive() ?? false;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'status'            => $this->status?->value,
            'customerId'        => $this->customerId->getValue(),
            'value'             => $this->value->getAmount(),
            'currency'          => $this->value->getCurrency(),
            'billingType'       => $this->billingType->value,
            'cycle'             => $this->cycle->value,
            'nextDueDate'       => $this->nextDueDate,
            'description'       => $this->description,
            'externalReference' => $this->externalReference,
        ];
    }
}
