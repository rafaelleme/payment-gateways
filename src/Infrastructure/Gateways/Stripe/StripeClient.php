<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe;

use Rafaelleme\PaymentGateways\Infrastructure\Http\GuzzleHttpClient;
use Rafaelleme\PaymentGateways\Infrastructure\Http\HttpClientInterface;

class StripeClient
{
    private HttpClientInterface $http;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://api.stripe.com',
    ) {
        $this->http = new GuzzleHttpClient(
            baseUri: $baseUrl,
            headers: [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/json',
            ],
        );
    }

    // --- Payments ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createPaymentIntent(array $payload): array
    {
        return $this->http->post('/v1/payment_intents', ['form_params' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getPaymentIntent(string $intentId): array
    {
        return $this->http->get("/v1/payment_intents/{$intentId}");
    }

    // --- Customers ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createCustomer(array $payload): array
    {
        return $this->http->post('/v1/customers', ['form_params' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getCustomer(string $customerId): array
    {
        return $this->http->get("/v1/customers/{$customerId}");
    }

    // --- Products ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createProduct(array $payload): array
    {
        return $this->http->post('/v1/products', ['form_params' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getProduct(string $productId): array
    {
        return $this->http->get("/v1/products/{$productId}");
    }

    // --- Prices ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createPrice(array $payload): array
    {
        return $this->http->post('/v1/prices', ['form_params' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getPrice(string $priceId): array
    {
        return $this->http->get("/v1/prices/{$priceId}");
    }

    // --- Subscriptions ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createSubscription(array $payload): array
    {
        return $this->http->post('/v1/subscriptions', ['form_params' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->http->get("/v1/subscriptions/{$subscriptionId}");
    }

    /** @return array<string, mixed> */
    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->http->delete("/v1/subscriptions/{$subscriptionId}");
    }

    /** @return array<string, mixed> */
    public function getSubscriptionPayments(string $subscriptionId): array
    {
        return $this->http->get("/v1/invoices?subscription={$subscriptionId}");
    }

    // --- Payment Methods ---

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createPaymentMethod(array $payload): array
    {
        return $this->http->post('/v1/payment_methods', ['form_params' => $payload]);
    }

    /** @return array<string, mixed> */
    public function getPaymentMethod(string $paymentMethodId): array
    {
        return $this->http->get("/v1/payment_methods/{$paymentMethodId}");
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function attachPaymentMethod(string $paymentMethodId, array $payload): array
    {
        return $this->http->post(
            "/v1/payment_methods/{$paymentMethodId}/attach",
            ['form_params' => $payload],
        );
    }
}
