<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;

/**
 * Represents all webhook event types sent by the Asaas API.
 *
 * @see https://docs.asaas.com/reference/webhook-events
 */
enum AsaasWebhookEvent: string
{
    // --- Payment events ---
    case PAYMENT_CREATED                      = 'PAYMENT_CREATED';
    case PAYMENT_AWAITING_RISK_ANALYSIS       = 'PAYMENT_AWAITING_RISK_ANALYSIS';
    case PAYMENT_APPROVED_BY_RISK_ANALYSIS    = 'PAYMENT_APPROVED_BY_RISK_ANALYSIS';
    case PAYMENT_REPROVED_BY_RISK_ANALYSIS    = 'PAYMENT_REPROVED_BY_RISK_ANALYSIS';
    case PAYMENT_AUTHORIZED                   = 'PAYMENT_AUTHORIZED';
    case PAYMENT_CONFIRMED                    = 'PAYMENT_CONFIRMED';
    case PAYMENT_RECEIVED                     = 'PAYMENT_RECEIVED';
    case PAYMENT_OVERDUE                      = 'PAYMENT_OVERDUE';
    case PAYMENT_DELETED                      = 'PAYMENT_DELETED';
    case PAYMENT_RESTORED                     = 'PAYMENT_RESTORED';
    case PAYMENT_REFUNDED                     = 'PAYMENT_REFUNDED';
    case PAYMENT_PARTIALLY_REFUNDED           = 'PAYMENT_PARTIALLY_REFUNDED';
    case PAYMENT_REFUND_IN_PROGRESS           = 'PAYMENT_REFUND_IN_PROGRESS';
    case PAYMENT_CHARGEBACK_REQUESTED         = 'PAYMENT_CHARGEBACK_REQUESTED';
    case PAYMENT_CHARGEBACK_DISPUTE           = 'PAYMENT_CHARGEBACK_DISPUTE';
    case PAYMENT_AWAITING_CHARGEBACK_REVERSAL = 'PAYMENT_AWAITING_CHARGEBACK_REVERSAL';
    case PAYMENT_DUNNING_RECEIVED             = 'PAYMENT_DUNNING_RECEIVED';
    case PAYMENT_DUNNING_REQUESTED            = 'PAYMENT_DUNNING_REQUESTED';
    case PAYMENT_BANK_SLIP_VIEWED             = 'PAYMENT_BANK_SLIP_VIEWED';
    case PAYMENT_CHECKOUT_VIEWED              = 'PAYMENT_CHECKOUT_VIEWED';

    // --- Subscription events ---
    case SUBSCRIPTION_CREATED = 'SUBSCRIPTION_CREATED';
    case SUBSCRIPTION_UPDATED = 'SUBSCRIPTION_UPDATED';
    case SUBSCRIPTION_DELETED = 'SUBSCRIPTION_DELETED';
    case SUBSCRIPTION_EXPIRED = 'SUBSCRIPTION_EXPIRED';

    // --- Invoice events ---
    case INVOICE_CREATED   = 'INVOICE_CREATED';
    case INVOICE_UPDATED   = 'INVOICE_UPDATED';
    case INVOICE_OVERDUE   = 'INVOICE_OVERDUE';
    case INVOICE_PAID      = 'INVOICE_PAID';
    case INVOICE_CANCELLED = 'INVOICE_CANCELLED';

    // --- Transfer events ---
    case TRANSFER_CREATED            = 'TRANSFER_CREATED';
    case TRANSFER_PENDING            = 'TRANSFER_PENDING';
    case TRANSFER_IN_BANK_PROCESSING = 'TRANSFER_IN_BANK_PROCESSING';
    case TRANSFER_DONE               = 'TRANSFER_DONE';
    case TRANSFER_FAILED             = 'TRANSFER_FAILED';
    case TRANSFER_CANCELLED          = 'TRANSFER_CANCELLED';

    // --- Bill events ---
    case BILL_CREATED         = 'BILL_CREATED';
    case BILL_PENDING         = 'BILL_PENDING';
    case BILL_BANK_PROCESSING = 'BILL_BANK_PROCESSING';
    case BILL_DONE            = 'BILL_DONE';
    case BILL_CANCELLED       = 'BILL_CANCELLED';
    case BILL_FAILED          = 'BILL_FAILED';

    /**
     * Maps the Asaas webhook event to the domain PaymentStatus.
     * Returns null if this event does not represent a payment status change.
     */
    public function toPaymentStatus(): ?PaymentStatus
    {
        return match ($this) {
            self::PAYMENT_CONFIRMED,
            self::PAYMENT_AUTHORIZED => PaymentStatus::CONFIRMED,

            self::PAYMENT_RECEIVED,
            self::PAYMENT_DUNNING_RECEIVED => PaymentStatus::RECEIVED,

            self::PAYMENT_OVERDUE,
            self::PAYMENT_DUNNING_REQUESTED => PaymentStatus::OVERDUE,

            self::PAYMENT_REFUNDED,
            self::PAYMENT_PARTIALLY_REFUNDED,
            self::PAYMENT_REFUND_IN_PROGRESS => PaymentStatus::REFUNDED,

            self::PAYMENT_CHARGEBACK_REQUESTED,
            self::PAYMENT_CHARGEBACK_DISPUTE,
            self::PAYMENT_AWAITING_CHARGEBACK_REVERSAL => PaymentStatus::CANCELLED,

            self::PAYMENT_REPROVED_BY_RISK_ANALYSIS => PaymentStatus::FAILED,

            self::PAYMENT_CREATED,
            self::PAYMENT_AWAITING_RISK_ANALYSIS,
            self::PAYMENT_APPROVED_BY_RISK_ANALYSIS,
            self::PAYMENT_RESTORED,
            self::PAYMENT_DELETED,
            self::PAYMENT_BANK_SLIP_VIEWED,
            self::PAYMENT_CHECKOUT_VIEWED => PaymentStatus::PENDING,

            default => null,
        };
    }

    /**
     * Returns the Laravel webhook event class to dispatch for this Asaas event.
     * Returns null if this event should be silently ignored.
     *
     * @return 'received'|'overdue'|'refused'|null
     */
    public function toDispatchEvent(): ?string
    {
        return match ($this) {
            self::PAYMENT_RECEIVED,
            self::PAYMENT_CONFIRMED,
            self::PAYMENT_DUNNING_RECEIVED => 'received',

            self::PAYMENT_OVERDUE,
            self::PAYMENT_DUNNING_REQUESTED => 'overdue',

            self::PAYMENT_CHARGEBACK_REQUESTED,
            self::PAYMENT_CHARGEBACK_DISPUTE,
            self::PAYMENT_AWAITING_CHARGEBACK_REVERSAL,
            self::PAYMENT_REPROVED_BY_RISK_ANALYSIS => 'refused',

            default => null,
        };
    }
}
