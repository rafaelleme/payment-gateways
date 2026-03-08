<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\CreditCard;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\CreditCardToken;

class AsaasCreditCardMapper
{
    /** @param array<string, mixed> $data */
    public function toToken(array $data): CreditCardToken
    {
        return new CreditCardToken(
            token:       (string) ($data['creditCardToken'] ?? ''),
            brand:       (string) ($data['creditCardBrand'] ?? ''),
            last4Digits: (string) ($data['creditCardNumber'] ?? ''),
        );
    }
}
