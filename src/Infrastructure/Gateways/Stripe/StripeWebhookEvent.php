<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe;

enum StripeWebhookEvent: string
{
    case PAYMENT_INTENT_SUCCEEDED      = 'payment_intent.succeeded';
    case PAYMENT_INTENT_PAYMENT_FAILED = 'payment_intent.payment_failed';
    case PAYMENT_INTENT_CANCELED       = 'payment_intent.canceled';
    case INVOICE_PAYMENT_SUCCEEDED     = 'invoice.payment_succeeded';
    case INVOICE_PAYMENT_FAILED        = 'invoice.payment_failed';
    case INVOICE_UPCOMING              = 'invoice.upcoming';
    case CHARGE_REFUNDED               = 'charge.refunded';
    case CHARGE_DISPUTE_CREATED        = 'charge.dispute.created';
    case SUBSCRIPTION_DELETED          = 'customer.subscription.deleted';

    public function isPaymentSuccess(): bool
    {
        return in_array($this, [
            self::PAYMENT_INTENT_SUCCEEDED,
            self::INVOICE_PAYMENT_SUCCEEDED,
        ]);
    }

    public function isPaymentFailure(): bool
    {
        return in_array($this, [
            self::PAYMENT_INTENT_PAYMENT_FAILED,
            self::INVOICE_PAYMENT_FAILED,
        ]);
    }

    public function isPaymentDispute(): bool
    {
        return $this === self::CHARGE_DISPUTE_CREATED;
    }
}
