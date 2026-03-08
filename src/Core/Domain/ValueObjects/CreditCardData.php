<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

final readonly class CreditCardData
{
    public function __construct(
        public string $holderName,
        public string $number,
        public string $expiryMonth,
        public string $expiryYear,
        public string $ccv,
    ) {
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'holderName'  => $this->holderName,
            'number'      => $this->number,
            'expiryMonth' => $this->expiryMonth,
            'expiryYear'  => $this->expiryYear,
            'ccv'         => $this->ccv,
        ];
    }
}
