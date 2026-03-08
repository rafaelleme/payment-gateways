<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

enum SubscriptionStatus: string
{
    case ACTIVE   = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case EXPIRED  = 'EXPIRED';

    public static function fromAsaas(string $value): self
    {
        return match ($value) {
            'ACTIVE'  => self::ACTIVE,
            'EXPIRED' => self::EXPIRED,
            default   => self::INACTIVE,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Ativa',
            self::INACTIVE => 'Inativa',
            self::EXPIRED  => 'Expirada',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
