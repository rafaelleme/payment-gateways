<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\PaymentStatus;

class FakeGateway implements PaymentGateway
{
    /** @var array<string, Payment> */
    private array $payments = [];

    private int $sequence = 1;

    public function createPayment(Payment $payment): Payment
    {
        $id = 'fake_pay_' . $this->sequence++;

        $created = new Payment(
            customerId:        $payment->customerId,
            value:             $payment->value,
            billingType:       $payment->billingType,
            dueDate:           $payment->dueDate,
            description:       $payment->description,
            externalReference: $payment->externalReference,
            id:                $id,
            status:            PaymentStatus::PENDING,
            invoiceUrl:        'https://fake.gateway/invoice/' . $id,
        );

        $this->payments[$id] = $created;

        return $created;
    }

    public function getPayment(string $paymentId): Payment
    {
        if (!isset($this->payments[$paymentId])) {
            throw new \RuntimeException("Payment [{$paymentId}] not found in FakeGateway.");
        }

        return $this->payments[$paymentId];
    }

    /** Helpers for test assertions */

    public function hasPayment(string $paymentId): bool
    {
        return isset($this->payments[$paymentId]);
    }

    /** @return array<string, Payment> */
    public function allPayments(): array
    {
        return $this->payments;
    }

    public function reset(): void
    {
        $this->payments = [];
        $this->sequence = 1;
    }
}
