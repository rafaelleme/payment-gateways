<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Enums;

enum PaymentStatus: string
{
    case PENDING   = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case RECEIVED  = 'RECEIVED';
    case OVERDUE   = 'OVERDUE';
    case REFUNDED  = 'REFUNDED';
    case CANCELLED = 'CANCELLED';
    case FAILED    = 'FAILED';

    public static function fromAsaas(string $value): self
    {
        return match ($value) {
            'CONFIRMED' => self::CONFIRMED,
            'RECEIVED', 'DUNNING_RECEIVED' => self::RECEIVED,
            'OVERDUE', 'DUNNING_REQUESTED' => self::OVERDUE,
            'REFUNDED', 'REFUND_IN_PROGRESS' => self::REFUNDED,
            'CHARGEBACK_REQUESTED', 'CHARGEBACK_DISPUTE' => self::CANCELLED,
            default => self::PENDING,
        };
    }

    public static function fromStripe(string $value): self
    {
        return match ($value) {
            'succeeded'               => self::RECEIVED,
            'processing'              => self::CONFIRMED,
            'requires_payment_method' => self::PENDING,
            'requires_action'         => self::PENDING,
            'requires_capture'        => self::CONFIRMED,
            'canceled'                => self::CANCELLED,
            default                   => self::FAILED,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Aguardando Pagamento',
            self::CONFIRMED => 'Confirmado',
            self::RECEIVED  => 'Recebido',
            self::OVERDUE   => 'Vencido',
            self::REFUNDED  => 'Estornado',
            self::CANCELLED => 'Cancelado',
            self::FAILED    => 'Falhou',
        };
    }

    public function isPaid(): bool
    {
        return in_array($this, [self::CONFIRMED, self::RECEIVED]);
    }

    public function isFailure(): bool
    {
        return in_array($this, [self::OVERDUE, self::FAILED, self::CANCELLED]);
    }
}
