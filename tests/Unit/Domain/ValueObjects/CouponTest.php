<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Domain\ValueObjects;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Coupon;

class CouponTest extends TestCase
{
    // --- Percentage Coupons ---

    public function test_can_create_percentage_coupon_with_valid_data(): void
    {
        $coupon = Coupon::percentage('DISCOUNT50', '50');

        $this->assertSame('DISCOUNT50', $coupon->getCode());
        $this->assertSame('50', $coupon->getValue());
        $this->assertTrue($coupon->isPercentage());
        $this->assertFalse($coupon->isFixedAmount());
        $this->assertSame('percentage', $coupon->getType());
    }

    public function test_throws_exception_when_percentage_code_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon code cannot be empty.');

        Coupon::percentage('', '50');
    }

    public function test_throws_exception_when_percentage_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon discount percentage must be between 0 and 100.');

        Coupon::percentage('DISCOUNT50', '-10');
    }

    public function test_throws_exception_when_percentage_exceeds_100(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon discount percentage must be between 0 and 100.');

        Coupon::percentage('DISCOUNT50', '150');
    }

    public function test_percentage_equals_returns_true_for_identical_coupons(): void
    {
        $coupon1 = Coupon::percentage('DISCOUNT50', '50');
        $coupon2 = Coupon::percentage('DISCOUNT50', '50');

        $this->assertTrue($coupon1->equals($coupon2));
    }

    public function test_percentage_equals_returns_false_for_different_codes(): void
    {
        $coupon1 = Coupon::percentage('DISCOUNT50', '50');
        $coupon2 = Coupon::percentage('DISCOUNT30', '50');

        $this->assertFalse($coupon1->equals($coupon2));
    }

    public function test_percentage_equals_returns_false_for_different_values(): void
    {
        $coupon1 = Coupon::percentage('DISCOUNT50', '50');
        $coupon2 = Coupon::percentage('DISCOUNT50', '30');

        $this->assertFalse($coupon1->equals($coupon2));
    }

    public function test_percentage_to_string_returns_formatted_coupon(): void
    {
        $coupon = Coupon::percentage('DISCOUNT50', '50');

        $this->assertSame('DISCOUNT50 (50%)', (string) $coupon);
    }

    public function test_get_discount_percentage_returns_value(): void
    {
        $coupon = Coupon::percentage('DISCOUNT50', '50');

        $this->assertSame('50', $coupon->getDiscountPercentage());
    }

    public function test_get_discount_percentage_throws_for_fixed_amount(): void
    {
        $coupon = Coupon::fixedAmount('SAVE10', '10');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('This coupon is not percentage-based.');

        $coupon->getDiscountPercentage();
    }

    // --- Fixed Amount Coupons ---

    public function test_can_create_fixed_amount_coupon_with_valid_data(): void
    {
        $coupon = Coupon::fixedAmount('SAVE10', '10.00');

        $this->assertSame('SAVE10', $coupon->getCode());
        $this->assertSame('10.00', $coupon->getValue());
        $this->assertTrue($coupon->isFixedAmount());
        $this->assertFalse($coupon->isPercentage());
        $this->assertSame('fixed_amount', $coupon->getType());
    }

    public function test_throws_exception_when_fixed_amount_code_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon code cannot be empty.');

        Coupon::fixedAmount('', '10.00');
    }

    public function test_throws_exception_when_fixed_amount_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Coupon discount amount cannot be negative.');

        Coupon::fixedAmount('SAVE10', '-10');
    }

    public function test_fixed_amount_equals_returns_true_for_identical_coupons(): void
    {
        $coupon1 = Coupon::fixedAmount('SAVE10', '10.00');
        $coupon2 = Coupon::fixedAmount('SAVE10', '10.00');

        $this->assertTrue($coupon1->equals($coupon2));
    }

    public function test_fixed_amount_equals_returns_false_for_different_codes(): void
    {
        $coupon1 = Coupon::fixedAmount('SAVE10', '10.00');
        $coupon2 = Coupon::fixedAmount('SAVE20', '10.00');

        $this->assertFalse($coupon1->equals($coupon2));
    }

    public function test_fixed_amount_equals_returns_false_for_different_values(): void
    {
        $coupon1 = Coupon::fixedAmount('SAVE10', '10.00');
        $coupon2 = Coupon::fixedAmount('SAVE10', '20.00');

        $this->assertFalse($coupon1->equals($coupon2));
    }

    public function test_fixed_amount_to_string_returns_formatted_coupon(): void
    {
        $coupon = Coupon::fixedAmount('SAVE10', '10.00');

        $this->assertSame('SAVE10 (-10.00)', (string) $coupon);
    }

    // --- Type comparisons ---

    public function test_different_types_are_not_equal(): void
    {
        $percentage = Coupon::percentage('DISCOUNT50', '50');
        $fixed      = Coupon::fixedAmount('SAVE10', '10');

        $this->assertFalse($percentage->equals($fixed));
    }

    public function test_percentage_zero_is_valid(): void
    {
        $coupon = Coupon::percentage('DUMMY', '0');

        $this->assertSame('0', $coupon->getValue());
        $this->assertTrue($coupon->isPercentage());
    }

    public function test_fixed_amount_zero_is_valid(): void
    {
        $coupon = Coupon::fixedAmount('DUMMY', '0');

        $this->assertSame('0', $coupon->getValue());
        $this->assertTrue($coupon->isFixedAmount());
    }
}
