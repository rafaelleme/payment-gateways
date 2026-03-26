<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Application\Services;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Coupon;

class CouponService
{
    public function __construct(
        protected GatewayContract $gateway,
    ) {
    }

    public function applyCouponToSubscription(string $subscriptionId, Coupon $coupon): Subscription
    {
        // For Stripe gateway
        if (method_exists($this->gateway, 'applyCouponToSubscription')) {
            return $this->gateway->applyCouponToSubscription($subscriptionId, $coupon->getCode());
        }

        throw new \BadMethodCallException('The current gateway does not support applying coupons to subscriptions.');
    }
}
