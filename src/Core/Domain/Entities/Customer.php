<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Entities;

readonly class Customer
{
    public function __construct(
        public string  $name,
        public string  $email,
        public ?string $phone = null,
        public ?string $cpfCnpj = null,
        public ?string $id = null,
        public ?string $externalReference = null,
    ) {
    }

    public function isPersisted(): bool
    {
        return $this->id !== null;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'cpfCnpj'           => $this->cpfCnpj,
            'externalReference' => $this->externalReference,
        ];
    }
}
