<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Entities;

readonly class CreditCardToken
{
    public function __construct(
        public string $token,   // creditCardToken
        public string $brand,   // creditCardBrand
        public string $last4Digits, // creditCardNumber
    ) {
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'token'       => $this->token,
            'brand'       => $this->brand,
            'last4Digits' => $this->last4Digits,
        ];
    }
}
