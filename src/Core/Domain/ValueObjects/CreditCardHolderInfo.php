<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\ValueObjects;

final readonly class CreditCardHolderInfo
{
    public function __construct(
        public string  $name,
        public string  $email,
        public string  $cpfCnpj,
        public string  $postalCode,
        public string  $addressNumber,
        public ?string $phone = null,
        public ?string $addressComplement = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name'              => $this->name,
            'email'             => $this->email,
            'cpfCnpj'           => $this->cpfCnpj,
            'postalCode'        => $this->postalCode,
            'addressNumber'     => $this->addressNumber,
            'phone'             => $this->phone,
            'addressComplement' => $this->addressComplement,
        ];
    }
}
