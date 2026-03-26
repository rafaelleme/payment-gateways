<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Coupons;

use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Coupon;

class StripeCouponMapper
{
    /** @param array<string, mixed> $data */
    public function toCoupon(array $data): Coupon
    {
        $discountPercentage = (float) ($data['percent_off'] ?? 0);

        return new Coupon(
            code:                (string) ($data['id'] ?? ''),
            discountPercentage:  (string) $discountPercentage,
        );
    }
}
