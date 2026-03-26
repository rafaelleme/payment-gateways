<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Coupons;

use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Coupon;

class StripeCouponMapper
{
    /** @param array<string, mixed> $data */
    public function toCoupon(array $data): Coupon
    {
        // Stripe supports both percent_off and amount_off
        if (isset($data['percent_off']) && $data['percent_off'] > 0) {
            return Coupon::percentage(
                code:    (string) ($data['id'] ?? ''),
                percent: (string) $data['percent_off'],
            );
        }

        if (isset($data['amount_off']) && $data['amount_off'] > 0) {
            // amount_off is in cents, convert to dollars
            $amountInDollars = (int) $data['amount_off'] / 100;

            return Coupon::fixedAmount(
                code:   (string) ($data['id'] ?? ''),
                amount: (string) $amountInDollars,
            );
        }

        // Default to percentage if neither is set (shouldn't happen in real Stripe data)
        return Coupon::percentage(
            code:    (string) ($data['id'] ?? ''),
            percent: '0',
        );
    }
}
