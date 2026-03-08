<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Contracts;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;

interface PaymentRepositoryContract
{
    public function save(string $gateway, Payment $payment, ?int $userId = null, ?int $localSubscriptionId = null): void;

    public function updateStatus(string $gateway, string $gatewayPaymentId, string $status): void;

    public function findByGatewayId(string $gateway, string $gatewayPaymentId): ?Payment;
}
