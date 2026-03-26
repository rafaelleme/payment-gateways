<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

final class Coupon
{
    private readonly string $type; // 'percentage' or 'fixed_amount'
    private readonly string $value;

    /**
     * Create a percentage-based coupon (0-100).
     *
     * @param string $code    Coupon code (e.g., 'DISCOUNT50')
     * @param string $percent Discount percentage (0-100)
     */
    public static function percentage(string $code, string $percent): self
    {
        return new self($code, $percent, 'percentage');
    }

    /**
     * Create a fixed amount coupon (currency-agnostic).
     *
     * @param string $code   Coupon code (e.g., 'SAVE10')
     * @param string $amount Fixed discount amount (e.g., '10.00')
     */
    public static function fixedAmount(string $code, string $amount): self
    {
        return new self($code, $amount, 'fixed_amount');
    }

    private function __construct(
        private readonly string $code,
        string $value,
        string $type,
    ) {
        if (empty(trim($code))) {
            throw new \InvalidArgumentException('Coupon code cannot be empty.');
        }

        if ($type === 'percentage') {
            if ((float) $value < 0 || (float) $value > 100) {
                throw new \InvalidArgumentException('Coupon discount percentage must be between 0 and 100.');
            }
        } elseif ($type === 'fixed_amount') {
            if ((float) $value < 0) {
                throw new \InvalidArgumentException('Coupon discount amount cannot be negative.');
            }
        } else {
            throw new \InvalidArgumentException("Invalid coupon type: {$type}");
        }

        $this->type  = $type;
        $this->value = $value;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPercentage(): bool
    {
        return $this->type === 'percentage';
    }

    public function isFixedAmount(): bool
    {
        return $this->type === 'fixed_amount';
    }

    /**
     * @deprecated Use isPercentage() instead
     */
    public function getDiscountPercentage(): string
    {
        if ($this->type !== 'percentage') {
            throw new \LogicException('This coupon is not percentage-based.');
        }

        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->code  === $other->code
            && $this->type  === $other->type
            && $this->value === $other->value;
    }

    public function __toString(): string
    {
        if ($this->type === 'percentage') {
            return "{$this->code} ({$this->value}%)";
        }

        return "{$this->code} (-{$this->value})";
    }
}
