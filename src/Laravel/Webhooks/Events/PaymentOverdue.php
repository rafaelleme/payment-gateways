<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks\Events;

readonly class PaymentOverdue
{
    /** @param array<string, mixed> $payment */
    public function __construct(
        public array $payment,
    ) {
    }
}
