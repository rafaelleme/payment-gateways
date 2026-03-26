<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Subscriptions;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionStatus;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Coupon;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Coupons\StripeCouponMapper;

class StripeSubscriptionMapper
{
    /** @param array<string, mixed> $data */
    public function toSubscription(array $data): Subscription
    {
        $items     = $data['items']['data'] ?? [];
        $priceData = !empty($items) ? $items[0]['price'] : [];

        $billingCycle = $this->mapBillingCycle($priceData);
        $value        = new Money((float) ($priceData['unit_amount'] ?? 0) / 100);

        $priceId         = isset($items[0]['price']['id']) ? (string) $items[0]['price']['id'] : null;
        $paymentMethodId = isset($data['default_payment_method']) ? (string) $data['default_payment_method'] : null;

        // Extract coupon from discount if present
        $coupon   = null;
        $discount = $data['discount'] ?? null;
        if ($discount && isset($discount['coupon'])) {
            $couponMapper = new StripeCouponMapper();
            $coupon       = $couponMapper->toCoupon($discount['coupon']);
        }

        return new Subscription(
            customerId:      new CustomerId((string) ($data['customer'] ?? '')),
            billingType:     BillingType::CREDIT_CARD,
            cycle:           $billingCycle,
            nextDueDate:     isset($data['current_period_end']) ? date('Y-m-d', (int) $data['current_period_end']) : date('Y-m-d'),
            value:           $value,
            description:     isset($data['description']) ? (string) $data['description'] : null,
            externalReference: isset($data['metadata']['externalReference']) ? (int) $data['metadata']['externalReference'] : null,
            id:              (string) $data['id'],
            status:          SubscriptionStatus::fromStripe((string) ($data['status'] ?? '')),
            priceId:         $priceId,
            paymentMethodId: $paymentMethodId,
            coupon:          $coupon,
        );
    }

    /** @param array<string, mixed> $priceData */
    private function mapBillingCycle(array $priceData): SubscriptionCycle
    {
        $interval      = $priceData['recurring']['interval'] ?? 'month';
        $intervalCount = (int) ($priceData['recurring']['interval_count'] ?? 1);

        if ($interval === 'week' && $intervalCount === 1) {
            return SubscriptionCycle::WEEKLY;
        }

        if ($interval === 'day' && $intervalCount === 14) {
            return SubscriptionCycle::BIWEEKLY;
        }

        if ($interval === 'month' && $intervalCount === 1) {
            return SubscriptionCycle::MONTHLY;
        }

        if ($interval === 'month' && $intervalCount === 3) {
            return SubscriptionCycle::QUARTERLY;
        }

        if ($interval === 'month' && $intervalCount === 6) {
            return SubscriptionCycle::SEMIANNUALLY;
        }

        if ($interval === 'year') {
            return SubscriptionCycle::YEARLY;
        }

        return SubscriptionCycle::MONTHLY;
    }
}
