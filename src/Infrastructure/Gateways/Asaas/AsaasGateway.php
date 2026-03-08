<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;

class AsaasGateway implements PaymentGateway
{
    protected Client $http;
    protected AsaasPaymentMapper $mapper;

    public function __construct(
        protected string $apiKey,
        protected string $baseUrl = 'https://api.asaas.com/v3',
    ) {
        $this->http = new Client([
            'base_uri' => rtrim($baseUrl, '/'),
            'headers'  => [
                'access_token' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ]);

        $this->mapper = new AsaasPaymentMapper();
    }

    /**
     * @throws GuzzleException
     */
    public function createPayment(Payment $payment): Payment
    {
        $response = $this->http->post('/payments', [
            'json' => [
                'customer'          => $payment->customerId->getValue(),
                'billingType'       => $payment->billingType->value,
                'value'             => $payment->value->getAmount(),
                'dueDate'           => $payment->dueDate,
                'description'       => $payment->description,
                'externalReference' => $payment->externalReference,
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        return $this->mapper->toPayment($data);
    }

    /**
     * @throws GuzzleException
     */
    public function getPayment(string $paymentId): Payment
    {
        $response = $this->http->get("/payments/{$paymentId}");

        $data = json_decode((string) $response->getBody(), true);

        return $this->mapper->toPayment($data);
    }
}
