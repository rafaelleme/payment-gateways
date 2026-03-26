<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

final class Coupon
{
    public function __construct(
        private readonly string $code,
        private readonly string $discountPercentage,
    ) {
        if (empty(trim($code))) {
            throw new \InvalidArgumentException('Coupon code cannot be empty.');
        }

        if ((float) $discountPercentage < 0 || (float) $discountPercentage > 100) {
            throw new \InvalidArgumentException('Coupon discount percentage must be between 0 and 100.');
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDiscountPercentage(): string
    {
        return $this->discountPercentage;
    }

    public function equals(self $other): bool
    {
        return $this->code               === $other->code
            && $this->discountPercentage === $other->discountPercentage;
    }

    public function __toString(): string
    {
        return "{$this->code} ({$this->discountPercentage}%)";
    }
}
