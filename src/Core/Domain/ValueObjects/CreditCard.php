<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

final readonly class CreditCard
{
    public function __construct(
        public string               $token,
        public CreditCardHolderInfo $holderInfo,
    ) {
    }
}
