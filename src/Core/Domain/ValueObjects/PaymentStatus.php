<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

enum PaymentStatus: string
{
    case PENDING   = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case RECEIVED  = 'RECEIVED';
    case OVERDUE   = 'OVERDUE';
    case REFUNDED  = 'REFUNDED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'Aguardando Pagamento',
            self::CONFIRMED => 'Confirmado',
            self::RECEIVED  => 'Recebido',
            self::OVERDUE   => 'Vencido',
            self::REFUNDED  => 'Estornado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function isPaid(): bool
    {
        return in_array($this, [self::CONFIRMED, self::RECEIVED]);
    }
}
