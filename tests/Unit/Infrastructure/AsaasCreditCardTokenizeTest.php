<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardToken;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\PaymentException;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardData;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasClient;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;

class AsaasCreditCardTokenizeTest extends TestCase
{
    private function makeCardData(): CreditCardData
    {
        return new CreditCardData(
            holderName:  'Rafael Leme',
            number:      '4111111111111111',
            expiryMonth: '12',
            expiryYear:  '2030',
            ccv:         '123',
        );
    }

    private function fakeTokenizeResponse(): array
    {
        return [
            'creditCardToken'  => '8608b88a-f74f-4f22-b3a1-dbbfc4c42cc9',
            'creditCardBrand'  => 'VISA',
            'creditCardNumber' => '1111',
        ];
    }

    public function test_tokenize_returns_credit_card_token_entity(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->expects($this->once())
            ->method('tokenizeCreditCard')
            ->with([
                'customer'   => 'cus_000007645678',
                'creditCard' => [
                    'holderName'  => 'Rafael Leme',
                    'number'      => '4111111111111111',
                    'expiryMonth' => '12',
                    'expiryYear'  => '2030',
                    'ccv'         => '123',
                ],
            ])
            ->willReturn($this->fakeTokenizeResponse());

        $result = (new AsaasGateway($client))->tokenizeCreditCard(
            'cus_000007645678',
            $this->makeCardData(),
        );

        $this->assertInstanceOf(CreditCardToken::class, $result);
        $this->assertSame('8608b88a-f74f-4f22-b3a1-dbbfc4c42cc9', $result->token);
        $this->assertSame('VISA', $result->brand);
        $this->assertSame('1111', $result->last4Digits);
    }

    public function test_tokenize_throws_on_api_error(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('tokenizeCreditCard')->willReturn([
            'errors' => [['description' => 'Invalid card number']],
        ]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Invalid card number');

        (new AsaasGateway($client))->tokenizeCreditCard('cus_123', $this->makeCardData());
    }

    public function test_tokenize_throws_on_empty_response(): void
    {
        $client = $this->createMock(AsaasClient::class);
        $client->method('tokenizeCreditCard')->willReturn([]);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Unexpected empty response from Asaas API.');

        (new AsaasGateway($client))->tokenizeCreditCard('cus_123', $this->makeCardData());
    }
}
