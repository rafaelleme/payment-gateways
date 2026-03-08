<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Http;

interface HttpClientInterface
{
    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function get(string $uri, array $options = []): array;

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function post(string $uri, array $options = []): array;

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function put(string $uri, array $options = []): array;

    /**
     * @param  array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function delete(string $uri, array $options = []): array;
}
