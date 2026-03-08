<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

final class Money
{
    public function __construct(
        private readonly float $amount,
        private readonly string $currency = 'BRL',
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function equals(self $other): bool
    {
        return $this->amount   === $other->amount
            && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2, '.', '') . ' ' . $this->currency;
    }
}
