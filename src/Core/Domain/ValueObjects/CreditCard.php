<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class CreditCard
{
    public function __construct(
        public CreditCardHolderInfo $holderInfo,
        public ?string              $token = null,
        public ?CreditCardData      $cardData = null,
    ) {
        if ($this->token === null && $this->cardData === null) {
            throw new InvalidArgumentException(
                'CreditCard requires either a token or card data (number, expiry, ccv).',
            );
        }
    }
}
