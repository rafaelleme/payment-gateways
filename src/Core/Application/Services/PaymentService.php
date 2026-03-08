<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Application\Services;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;

class PaymentService
{
    public function __construct(
        protected PaymentGateway $gateway,
    ) {
    }

    public function create(Payment $payment): Payment
    {
        return $this->gateway->createPayment($payment);
    }

    public function get(string $paymentId): Payment
    {
        return $this->gateway->getPayment($paymentId);
    }
}
