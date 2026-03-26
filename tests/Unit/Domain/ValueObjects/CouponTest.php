<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Coupon;

class CouponTest extends TestCase
{
    public function test_can_create_coupon_with_valid_data(): void
    {
        $coupon = new Coupon(
            code:                'DISCOUNT50',
            discountPercentage:  '50',
        );

        $this->assertSame('DISCOUNT50', $coupon->getCode());
        $this->assertSame('50', $coupon->getDiscountPercentage());
    }

    public function test_throws_exception_when_code_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon code cannot be empty.');

        new Coupon(
            code:                '',
            discountPercentage:  '50',
        );
    }

    public function test_throws_exception_when_discount_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon discount percentage must be between 0 and 100.');

        new Coupon(
            code:                'DISCOUNT50',
            discountPercentage:  '-10',
        );
    }

    public function test_throws_exception_when_discount_exceeds_100(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon discount percentage must be between 0 and 100.');

        new Coupon(
            code:                'DISCOUNT50',
            discountPercentage:  '150',
        );
    }

    public function test_equals_returns_true_for_identical_coupons(): void
    {
        $coupon1 = new Coupon('DISCOUNT50', '50');
        $coupon2 = new Coupon('DISCOUNT50', '50');

        $this->assertTrue($coupon1->equals($coupon2));
    }

    public function test_equals_returns_false_for_different_codes(): void
    {
        $coupon1 = new Coupon('DISCOUNT50', '50');
        $coupon2 = new Coupon('DISCOUNT30', '50');

        $this->assertFalse($coupon1->equals($coupon2));
    }

    public function test_equals_returns_false_for_different_discounts(): void
    {
        $coupon1 = new Coupon('DISCOUNT50', '50');
        $coupon2 = new Coupon('DISCOUNT50', '30');

        $this->assertFalse($coupon1->equals($coupon2));
    }

    public function test_to_string_returns_formatted_coupon(): void
    {
        $coupon = new Coupon('DISCOUNT50', '50');

        $this->assertSame('DISCOUNT50 (50%)', (string) $coupon);
    }
}
