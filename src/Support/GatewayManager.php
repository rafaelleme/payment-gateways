<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Support;

use Closure;
use InvalidArgumentException;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;

class GatewayManager
{
    /** @var array<string, Closure> */
    private array $factories = [];

    /** @var array<string, GatewayContract> */
    private array $resolved = [];

    private string $defaultDriver;

    public function __construct(string $defaultDriver = '')
    {
        $this->defaultDriver = $defaultDriver;
    }

    /**
     * Register a gateway factory.
     */
    public function register(string $name, Closure $factory): static
    {
        $this->factories[$name] = $factory;

        return $this;
    }

    /**
     * Alias for register — allows extending with custom gateways.
     */
    public function extend(string $name, Closure $factory): static
    {
        return $this->register($name, $factory);
    }

    /**
     * Resolve and return a gateway driver instance.
     */
    public function driver(?string $name = null): GatewayContract
    {
        $name ??= $this->getDefaultDriver();

        if (!isset($this->factories[$name])) {
            $registered = implode(', ', array_keys($this->factories));
            throw new InvalidArgumentException(
                "Payment gateway [{$name}] is not registered. Registered gateways: {$registered}",
            );
        }

        if (!isset($this->resolved[$name])) {
            $this->resolved[$name] = ($this->factories[$name])();
        }

        return $this->resolved[$name];
    }

    /**
     * Set the default driver name.
     */
    public function setDefaultDriver(string $name): static
    {
        $this->defaultDriver = $name;

        return $this;
    }

    public function getDefaultDriver(): string
    {
        if (empty($this->defaultDriver)) {
            throw new InvalidArgumentException(
                'No default payment gateway driver has been configured.',
            );
        }

        return $this->defaultDriver;
    }

    /**
     * Check if a gateway is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->factories[$name]);
    }

    /**
     * Get list of registered gateway names.
     *
     * @return array<int, string>
     */
    public function getRegisteredGateways(): array
    {
        return array_keys($this->factories);
    }

    /**
     * Check if a specific gateway factory exists.
     */
    public function isRegistered(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Proxy method calls to the default driver.
     */
    /** @param array<int, mixed> $arguments */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->driver()->$method(...$arguments);
    }
}
