<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;

    /** @param array<string, mixed> $headers */
    public function __construct(
        string $baseUri,
        array $headers = [],
    ) {
        $this->client = new Client([
            'base_uri' => rtrim($baseUri, '/'),
            'headers'  => $headers,
        ]);
    }

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     * @throws GuzzleException
     */
    public function get(string $uri, array $options = []): array
    {
        $response = $this->client->get($uri, $options);

        return $this->decode($response->getBody()->getContents());
    }

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     * @throws GuzzleException
     */
    public function post(string $uri, array $options = []): array
    {
        $response = $this->client->post($uri, $options);

        return $this->decode($response->getBody()->getContents());
    }

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     * @throws GuzzleException
     */
    public function put(string $uri, array $options = []): array
    {
        $response = $this->client->put($uri, $options);

        return $this->decode($response->getBody()->getContents());
    }

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     * @throws GuzzleException
     */
    public function delete(string $uri, array $options = []): array
    {
        $response = $this->client->delete($uri, $options);

        return $this->decode($response->getBody()->getContents());
    }

    /** @return array<string, mixed> */
    private function decode(string $body): array
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body, true) ?? [];

        return $decoded;
    }
}
