<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Rafaelleme\PaymentGateways\Infrastructure\Http\GuzzleHttpClient;
use Rafaelleme\PaymentGateways\Infrastructure\Http\HttpClientInterface;

class AsaasClient
{
    private HttpClientInterface $http;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.asaas.com/v3',
    ) {
        $this->http = new GuzzleHttpClient(
            baseUri: $baseUrl,
            headers: [
                'access_token' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        );
    }

    /** @return array<string, mixed> */
    public function getPayment(string $paymentId): array
    {
        return $this->http->get("/payments/{$paymentId}");
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createPayment(array $payload): array
    {
        return $this->http->post('/payments', ['json' => $payload]);
    }
}
