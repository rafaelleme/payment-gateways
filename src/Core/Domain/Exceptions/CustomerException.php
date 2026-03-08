<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Exceptions;

use RuntimeException;

final class CustomerException extends RuntimeException
{
    public static function notFound(string $customerId): self
    {
        return new self("Customer [{$customerId}] not found.");
    }

    public static function apiError(string $message, int $code = 0): self
    {
        return new self("Asaas API error: {$message}", $code);
    }
}
