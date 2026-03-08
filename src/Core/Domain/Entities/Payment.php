<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Entities;

use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\PaymentStatus;

readonly class Payment
{
    public function __construct(
        public CustomerId     $customerId,
        public Money          $value,
        public BillingType    $billingType,
        public string         $dueDate,
        public ?string        $description = null,
        public ?string        $externalReference = null,
        public ?string        $id = null,
        public ?PaymentStatus $status = null,
        public ?string        $invoiceUrl = null,
    ) {
    }

    public function isPersisted(): bool
    {
        return $this->id !== null;
    }

    public function isPaid(): bool
    {
        return $this->status?->isPaid() ?? false;
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
            'dueDate'           => $this->dueDate,
            'description'       => $this->description,
            'externalReference' => $this->externalReference,
            'invoiceUrl'        => $this->invoiceUrl,
        ];
    }
}
