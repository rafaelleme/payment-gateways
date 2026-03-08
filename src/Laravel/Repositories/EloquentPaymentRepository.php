<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Repositories;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Laravel\Models\GatewayPayment;

class EloquentPaymentRepository implements PaymentRepositoryContract
{
    public function save(string $gateway, Payment $payment, ?int $userId = null, ?int $localSubscriptionId = null): void
    {
        GatewayPayment::updateOrCreate(
            [
                'gateway'            => $gateway,
                'gateway_payment_id' => $payment->id,
            ],
            [
                'user_id'         => $userId,
                'subscription_id' => $localSubscriptionId,
                'status'          => $payment->status?->value ?? 'PENDING',
                'billing_type'    => $payment->billingType->value,
                'value'           => $payment->value->getAmount(),
                'due_date'        => $payment->dueDate,
                'paid_at'         => $payment->isPaid() ? now() : null,
            ],
        );
    }

    public function updateStatus(string $gateway, string $gatewayPaymentId, string $status): void
    {
        $paidStatuses = ['RECEIVED', 'CONFIRMED'];

        GatewayPayment::where('gateway', $gateway)
            ->where('gateway_payment_id', $gatewayPaymentId)
            ->update([
                'status'  => $status,
                'paid_at' => in_array($status, $paidStatuses, true) ? now() : null,
            ]);
    }

    public function findByGatewayId(string $gateway, string $gatewayPaymentId): ?Payment
    {
        $record = GatewayPayment::where('gateway', $gateway)
            ->where('gateway_payment_id', $gatewayPaymentId)
            ->first();

        if ($record === null) {
            return null;
        }

        return new Payment(
            customerId:  new CustomerId(''),
            value:       new Money((float) $record->value),
            billingType: BillingType::from($record->billing_type),
            dueDate:     $record->due_date?->format('Y-m-d') ?? '',
            id:          $record->gateway_payment_id,
            status:      PaymentStatus::fromAsaas($record->status),
        );
    }
}
