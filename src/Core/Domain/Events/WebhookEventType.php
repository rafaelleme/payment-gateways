<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Events;

enum WebhookEventType: string
{
    case PAYMENT_CONFIRMED = 'PAYMENT_CONFIRMED';
    case PAYMENT_FAILED    = 'PAYMENT_FAILED';
    case PAYMENT_OVERDUE   = 'PAYMENT_OVERDUE';
    case REFUND_CREATED    = 'REFUND_CREATED';
    case CHARGEBACK        = 'CHARGEBACK';

    public function label(): string
    {
        return match($this) {
            self::PAYMENT_CONFIRMED => 'Pagamento Confirmado',
            self::PAYMENT_FAILED    => 'Pagamento Falhou',
            self::PAYMENT_OVERDUE   => 'Pagamento Vencido',
            self::REFUND_CREATED    => 'Estorno Criado',
            self::CHARGEBACK        => 'Chargeback',
        };
    }
}
