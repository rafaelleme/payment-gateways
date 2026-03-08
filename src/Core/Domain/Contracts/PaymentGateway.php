<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Contracts;

use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;

interface PaymentGateway
{
    public function createPayment(Payment $payment): Payment;

    public function getPayment(string $paymentId): Payment;
}
