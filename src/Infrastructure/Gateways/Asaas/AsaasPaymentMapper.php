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
    /** @param array<string, mixed> $data */
    public function toPayment(array $data): Payment
    {
        return new Payment(
            customerId:        new CustomerId((string) $data['customer']),
            value:             new Money((float) ($data['value'] ?? 0)),
            billingType:       BillingType::fromAsaas((string) ($data['billingType'] ?? '')),
            dueDate:           (string) ($data['dueDate'] ?? ''),
            description:       isset($data['description']) ? (string) $data['description'] : null,
            externalReference: isset($data['externalReference']) ? (string) $data['externalReference'] : null,
            id:                (string) $data['id'],
            status:            PaymentStatus::fromAsaas((string) ($data['status'] ?? '')),
            invoiceUrl:        isset($data['invoiceUrl']) ? (string) $data['invoiceUrl'] : null,
            pixQrCode:         isset($data['pixQrCode']) ? (string) $data['pixQrCode'] : null,
            pixKey:            isset($data['pixKey']) ? (string) $data['pixKey'] : null,
        );
    }
}
