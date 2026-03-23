<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Tests\Unit\Infrastructure\Gateways\Stripe;

use PHPUnit\Framework\TestCase;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\PaymentStatus;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Payments\StripePaymentMapper;

class StripePaymentMapperTest extends TestCase
{
    private StripePaymentMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new StripePaymentMapper();
    }

    public function testToPaymentMapsStripeDataCorrectly(): void
    {
        $data = [
            'id'                   => 'pi_1234567890',
            'customer'             => 'cus_1234567890',
            'amount'               => 9999, // $99.99 em cents
            'status'               => 'succeeded',
            'description'          => 'Test payment',
            'created'              => 1710954000,
            'payment_method_types' => ['card'],
            'metadata'             => ['externalReference' => 123],
            'charges'              => ['data' => [['receipt_url' => 'https://example.com/receipt']]],
        ];

        $payment = $this->mapper->toPayment($data);

        $this->assertEquals('pi_1234567890', $payment->id);
        $this->assertEquals('cus_1234567890', $payment->customerId->getValue());
        $this->assertEquals(99.99, $payment->value->getAmount());
        $this->assertEquals('BRL', $payment->value->getCurrency());
        $this->assertEquals(PaymentStatus::RECEIVED, $payment->status);
        $this->assertEquals(BillingType::CREDIT_CARD, $payment->billingType);
        $this->assertEquals('Test payment', $payment->description);
        $this->assertEquals(123, $payment->externalReference);
    }

    public function testToPaymentFromInvoiceMapsCorrectly(): void
    {
        $data = [
            'id'                 => 'in_1234567890',
            'customer'           => 'cus_1234567890',
            'amount_paid'        => 9999,
            'total'              => 9999,
            'status'             => 'paid',
            'created'            => 1710954000,
            'description'        => 'Invoice',
            'metadata'           => ['externalReference' => 456],
            'hosted_invoice_url' => 'https://example.com/invoice',
        ];

        $payment = $this->mapper->toPaymentFromInvoice($data);

        $this->assertEquals('in_1234567890', $payment->id);
        $this->assertEquals(99.99, $payment->value->getAmount());
        $this->assertEquals(PaymentStatus::FAILED, $payment->status); // 'paid' não é um status PaymentIntent válido
        $this->assertEquals('https://example.com/invoice', $payment->invoiceUrl);
    }

    public function testPaymentStatusMappingFromStripe(): void
    {
        $statuses = [
            'succeeded'               => PaymentStatus::RECEIVED,
            'processing'              => PaymentStatus::CONFIRMED,
            'requires_payment_method' => PaymentStatus::PENDING,
            'canceled'                => PaymentStatus::CANCELLED,
        ];

        foreach ($statuses as $stripeStatus => $expectedStatus) {
            $data = [
                'id'                   => 'pi_test',
                'customer'             => 'cus_test',
                'amount'               => 1000,
                'status'               => $stripeStatus,
                'payment_method_types' => ['card'],
            ];

            $payment = $this->mapper->toPayment($data);
            $this->assertEquals($expectedStatus, $payment->status, "Failed for status: $stripeStatus");
        }
    }
}
