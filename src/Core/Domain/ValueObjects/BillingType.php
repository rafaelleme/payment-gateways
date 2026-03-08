<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

enum BillingType: string
{
    case BOLETO      = 'BOLETO';
    case PIX         = 'PIX';
    case CREDIT_CARD = 'CREDIT_CARD';
    case DEBIT_CARD  = 'DEBIT_CARD';
    case TRANSFER    = 'TRANSFER';
    case UNDEFINED   = 'UNDEFINED';

    public static function fromAsaas(string $value): self
    {
        return match($value) {
            'BOLETO'      => self::BOLETO,
            'PIX'         => self::PIX,
            'CREDIT_CARD' => self::CREDIT_CARD,
            'DEBIT_CARD'  => self::DEBIT_CARD,
            'TRANSFER'    => self::TRANSFER,
            default       => self::UNDEFINED,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::BOLETO      => 'Boleto Bancário',
            self::PIX         => 'PIX',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::DEBIT_CARD  => 'Cartão de Débito',
            self::TRANSFER    => 'Transferência',
            self::UNDEFINED   => 'Indefinido',
        };
    }
}
