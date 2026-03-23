<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Payments;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;

class StripePaymentMapper
{
    /** @param array<string, mixed> $data */
    public function toPayment(array $data): Payment
    {
        return new Payment(
            customerId:        new CustomerId((string) ($data['customer'] ?? '')),
            value:             new Money((float) ($data['amount'] ?? 0) / 100),
            billingType:       BillingType::fromStripe((string) ($data['payment_method_types'][0] ?? 'card')),
            dueDate:           isset($data['created']) ? date('Y-m-d', (int) $data['created']) : date('Y-m-d'),
            description:       isset($data['description']) ? (string) $data['description'] : null,
            externalReference: isset($data['metadata']['externalReference']) ? (int) $data['metadata']['externalReference'] : null,
            id:                (string) $data['id'],
            status:            PaymentStatus::fromStripe((string) ($data['status'] ?? '')),
            invoiceUrl:        isset($data['charges']['data'][0]['receipt_url']) ? (string) $data['charges']['data'][0]['receipt_url'] : null,
        );
    }

    /** @param array<string, mixed> $data */
    public function toPaymentFromInvoice(array $data): Payment
    {
        $amountPaid = (float) ($data['amount_paid'] ?? 0) / 100;
        $total      = (float) ($data['total'] ?? 0)       / 100;

        return new Payment(
            customerId:        new CustomerId((string) ($data['customer'] ?? '')),
            value:             new Money($total),
            billingType:       BillingType::CREDIT_CARD,
            dueDate:           isset($data['created']) ? date('Y-m-d', (int) $data['created']) : date('Y-m-d'),
            description:       isset($data['description']) ? (string) $data['description'] : null,
            externalReference: isset($data['metadata']['externalReference']) ? (int) $data['metadata']['externalReference'] : null,
            id:                (string) $data['id'],
            status:            PaymentStatus::fromStripe((string) ($data['status'] ?? '')),
            invoiceUrl:        isset($data['hosted_invoice_url']) ? (string) $data['hosted_invoice_url'] : null,
        );
    }
}
