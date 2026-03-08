<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\PaymentStatus;

class AsaasPaymentMapper
{
    private const STATUS_MAP = [
        'PENDING'                => PaymentStatus::PENDING,
        'CONFIRMED'              => PaymentStatus::CONFIRMED,
        'RECEIVED'               => PaymentStatus::RECEIVED,
        'OVERDUE'                => PaymentStatus::OVERDUE,
        'REFUNDED'               => PaymentStatus::REFUNDED,
        'REFUND_IN_PROGRESS'     => PaymentStatus::REFUNDED,
        'CHARGEBACK_REQUESTED'   => PaymentStatus::CANCELLED,
        'CHARGEBACK_DISPUTE'     => PaymentStatus::CANCELLED,
        'DUNNING_REQUESTED'      => PaymentStatus::OVERDUE,
        'DUNNING_RECEIVED'       => PaymentStatus::RECEIVED,
        'AWAITING_RISK_ANALYSIS' => PaymentStatus::PENDING,
    ];

    private const BILLING_MAP = [
        'BOLETO'      => BillingType::BOLETO,
        'PIX'         => BillingType::PIX,
        'CREDIT_CARD' => BillingType::CREDIT_CARD,
        'DEBIT_CARD'  => BillingType::DEBIT_CARD,
        'TRANSFER'    => BillingType::TRANSFER,
        'UNDEFINED'   => BillingType::UNDEFINED,
    ];

    /** @param array<string, mixed> $data */
    public function toPayment(array $data): Payment
    {
        $status      = self::STATUS_MAP[$data['status'] ?? '']       ?? PaymentStatus::PENDING;
        $billingType = self::BILLING_MAP[$data['billingType'] ?? ''] ?? BillingType::UNDEFINED;

        return new Payment(
            customerId:         new CustomerId($data['customer']),
            value:              new Money((float) ($data['value'] ?? 0)),
            billingType:        $billingType,
            dueDate:            $data['dueDate']           ?? '',
            description:        $data['description']       ?? null,
            externalReference:  $data['externalReference'] ?? null,
            id:                 $data['id'],
            status:             $status,
            invoiceUrl:         $data['invoiceUrl'] ?? null,
        );
    }
}
