<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

enum SubscriptionCycle: string
{
    case WEEKLY       = 'WEEKLY';
    case BIWEEKLY     = 'BIWEEKLY';
    case MONTHLY      = 'MONTHLY';
    case QUARTERLY    = 'QUARTERLY';
    case SEMIANNUALLY = 'SEMIANNUALLY';
    case YEARLY       = 'YEARLY';

    public static function fromAsaas(string $value): self
    {
        return match ($value) {
            'WEEKLY'       => self::WEEKLY,
            'BIWEEKLY'     => self::BIWEEKLY,
            'QUARTERLY'    => self::QUARTERLY,
            'SEMIANNUALLY' => self::SEMIANNUALLY,
            'YEARLY'       => self::YEARLY,
            default        => self::MONTHLY,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::WEEKLY       => 'Semanal',
            self::BIWEEKLY     => 'Quinzenal',
            self::MONTHLY      => 'Mensal',
            self::QUARTERLY    => 'Trimestral',
            self::SEMIANNUALLY => 'Semestral',
            self::YEARLY       => 'Anual',
        };
    }
}
