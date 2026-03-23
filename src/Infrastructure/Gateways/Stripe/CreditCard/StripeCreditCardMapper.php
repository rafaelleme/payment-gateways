<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\CreditCard;

use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardToken;

class StripeCreditCardMapper
{
    /** @param array<string, mixed> $data */
    public function toToken(array $data): CreditCardToken
    {
        $card = $data['card'] ?? [];

        return new CreditCardToken(
            token:       (string) ($data['id'] ?? ''),
            brand:       (string) ($card['brand'] ?? ''),
            last4Digits: (string) ($card['last4'] ?? ''),
        );
    }
}
