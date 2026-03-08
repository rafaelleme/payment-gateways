<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Exceptions;

use RuntimeException;

final class PaymentException extends RuntimeException
{
    public static function notFound(string $paymentId): self
    {
        return new self("Payment [{$paymentId}] not found.");
    }

    public static function apiError(string $message, int $code = 0): self
    {
        return new self("Asaas API error: {$message}", $code);
    }
}
