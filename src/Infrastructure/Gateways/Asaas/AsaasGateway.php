<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;

class AsaasGateway implements PaymentGateway
{
    public function __construct(
        private readonly AsaasClient $client,
        private readonly AsaasPaymentMapper $mapper = new AsaasPaymentMapper(),
    ) {
    }

    public function createPayment(Payment $payment): Payment
    {
        $data = $this->client->createPayment([
            'customer'          => $payment->customerId->getValue(),
            'billingType'       => $payment->billingType->value,
            'value'             => $payment->value->getAmount(),
            'dueDate'           => $payment->dueDate,
            'description'       => $payment->description,
            'externalReference' => $payment->externalReference,
        ]);

        return $this->mapper->toPayment($data);
    }

    public function getPayment(string $paymentId): Payment
    {
        $data = $this->client->getPayment($paymentId);

        return $this->mapper->toPayment($data);
    }
}
