<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

final class CustomerId
{
    public function __construct(
        private readonly string $value,
    ) {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('CustomerId cannot be empty.');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
