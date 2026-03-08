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
        string $baseUrl = 'https://api.asaas.com',
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

    // --- Payments ---

    /** @return array<string, mixed> */
    public function getPayment(string $paymentId): array
    {
        return $this->http->get("/v3/payments/{$paymentId}");
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createPayment(array $payload): array
    {
        return $this->http->post('/v3/payments', ['json' => $payload]);
    }

    // --- Customers ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createCustomer(array $payload): array
    {
        return $this->http->post('/v3/customers', ['json' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getCustomer(string $customerId): array
    {
        return $this->http->get("/v3/customers/{$customerId}");
    }

    // --- Subscriptions ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createSubscription(array $payload): array
    {
        return $this->http->post('/v3/subscriptions', ['json' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->http->get("/v3/subscriptions/{$subscriptionId}");
    }

    /** @return array<string, mixed> */
    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->http->delete("/v3/subscriptions/{$subscriptionId}");
    }

    /** @return array<string, mixed> */
    public function getSubscriptionPayments(string $subscriptionId): array
    {
        return $this->http->get("/v3/subscriptions/{$subscriptionId}/payments");
    }

    // --- Credit Card ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function tokenizeCreditCard(array $payload): array
    {
        return $this->http->post('/v3/creditCard/tokenize', ['json' => $payload]);
    }
}
