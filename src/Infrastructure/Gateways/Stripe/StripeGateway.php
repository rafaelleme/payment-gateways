<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\CustomerException;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\PaymentException;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardData;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardToken;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\CreditCard\StripeCreditCardMapper;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Customers\StripeCustomerMapper;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Payments\StripePaymentMapper;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Stripe\Subscriptions\StripeSubscriptionMapper;

readonly class StripeGateway implements GatewayContract
{
    private StripePaymentMapper $paymentMapper;
    private StripeCustomerMapper $customerMapper;
    private StripeSubscriptionMapper $subscriptionMapper;
    private StripeCreditCardMapper $creditCardMapper;
    private LoggerInterface $logger;

    public function __construct(
        private StripeClient $client,
        ?LoggerInterface     $logger = null,
    ) {
        $this->paymentMapper      = new StripePaymentMapper();
        $this->customerMapper     = new StripeCustomerMapper();
        $this->subscriptionMapper = new StripeSubscriptionMapper();
        $this->creditCardMapper   = new StripeCreditCardMapper();
        $this->logger             = $logger ?? new NullLogger();
    }

    // --- Payments ---

    public function createPayment(Payment $payment): Payment
    {
        $payload = [
            'customer'                    => $payment->customerId->getValue(),
            'amount'                      => (int) ($payment->value->getAmount() * 100),
            'currency'                    => strtolower($payment->value->getCurrency()),
            'payment_method_types'        => ['card'],
            'description'                 => $payment->description,
            'metadata[externalReference]' => $payment->externalReference,
        ];

        $this->logger->info('stripe.createPayment: request', ['payload' => $payload]);

        $data = $this->client->createPaymentIntent($payload);

        $this->logger->info('stripe.createPayment: response', ['data' => $data]);

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown error';
            $this->logger->error('stripe.createPayment: api error', ['message' => $message]);
            throw PaymentException::apiError($message);
        }

        if (empty($data['id'])) {
            $this->logger->error('stripe.createPayment: unexpected empty response', ['data' => $data]);
            throw PaymentException::apiError('Unexpected empty response from Stripe API.');
        }

        return $this->paymentMapper->toPayment($data);
    }

    public function getPayment(string $paymentId): Payment
    {
        $this->logger->info('stripe.getPayment: request', ['paymentId' => $paymentId]);

        $data = $this->client->getPaymentIntent($paymentId);

        $this->logger->info('stripe.getPayment: response', ['data' => $data]);

        if (empty($data['id'])) {
            $this->logger->warning('stripe.getPayment: not found', ['paymentId' => $paymentId]);
            throw PaymentException::notFound($paymentId);
        }

        return $this->paymentMapper->toPayment($data);
    }

    // --- Customers ---

    public function createCustomer(Customer $customer): Customer
    {
        $payload = [
            'name'                        => $customer->name,
            'email'                       => $customer->email,
            'phone'                       => $customer->phone,
            'metadata[cpfCnpj]'           => $customer->cpfCnpj,
            'metadata[externalReference]' => $customer->externalReference,
        ];

        $this->logger->info('stripe.createCustomer: request', ['payload' => $payload]);

        $data = $this->client->createCustomer($payload);

        $this->logger->info('stripe.createCustomer: response', ['data' => $data]);

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown error';
            $this->logger->error('stripe.createCustomer: api error', ['message' => $message]);
            throw CustomerException::apiError($message);
        }

        if (empty($data['id'])) {
            $this->logger->error('stripe.createCustomer: unexpected empty response', ['data' => $data]);
            throw CustomerException::apiError('Unexpected empty response from Stripe API.');
        }

        return $this->customerMapper->toCustomer($data);
    }

    public function getCustomer(string $customerId): Customer
    {
        $this->logger->info('stripe.getCustomer: request', ['customerId' => $customerId]);

        $data = $this->client->getCustomer($customerId);

        $this->logger->info('stripe.getCustomer: response', ['data' => $data]);

        if (empty($data['id'])) {
            $this->logger->warning('stripe.getCustomer: not found', ['customerId' => $customerId]);
            throw CustomerException::notFound($customerId);
        }

        return $this->customerMapper->toCustomer($data);
    }

    // --- Subscriptions ---

    public function createSubscription(Subscription $subscription): Subscription
    {
        if ($subscription->priceId === null) {
            throw SubscriptionException::apiError('Price ID is required for Stripe subscriptions. Please provide a priceId.');
        }

        // Create the subscription with pre-created price
        $subscriptionPayload = [
            'customer' => $subscription->customerId->getValue(),
            'items'    => [
                [
                    'price' => $subscription->priceId,
                ],
            ],
            'payment_behavior'            => 'default_incomplete',
            'metadata' => [
                'externalReference' => $subscription->externalReference,
            ],
            'description'                 => $subscription->description,
        ];

        // Use provided paymentMethodId or creditCard token
        if ($subscription->paymentMethodId !== null) {
            $subscriptionPayload['default_payment_method'] = $subscription->paymentMethodId;
            $this->logger->info('stripe.createSubscription: using provided paymentMethodId', ['paymentMethodId' => $subscription->paymentMethodId]);
        } elseif ($subscription->creditCard !== null) {
            if ($subscription->creditCard->token !== null) {
                $subscriptionPayload['default_payment_method'] = $subscription->creditCard->token;
            }
        }

        $this->logger->info('stripe.createSubscription: request', ['payload' => $subscriptionPayload]);
        $data = $this->client->createSubscription($subscriptionPayload);

        $this->logger->info('stripe.createSubscription: response', ['data' => $data]);

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown error';
            $this->logger->error('stripe.createSubscription: api error', ['message' => $message]);
            throw SubscriptionException::apiError($message);
        }

        if (empty($data['id'])) {
            $this->logger->error('stripe.createSubscription: unexpected empty response', ['data' => $data]);
            throw SubscriptionException::apiError('Unexpected empty response from Stripe API.');
        }

        return $this->subscriptionMapper->toSubscription($data);
    }

    public function getSubscription(string $subscriptionId): Subscription
    {
        $this->logger->info('stripe.getSubscription: request', ['subscriptionId' => $subscriptionId]);

        $data = $this->client->getSubscription($subscriptionId);

        $this->logger->info('stripe.getSubscription: response', ['data' => $data]);

        if (empty($data['id'])) {
            $this->logger->warning('stripe.getSubscription: not found', ['subscriptionId' => $subscriptionId]);
            throw SubscriptionException::subscriptionNotFound($subscriptionId);
        }

        return $this->subscriptionMapper->toSubscription($data);
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        $this->logger->info('stripe.cancelSubscription: request', ['subscriptionId' => $subscriptionId]);

        $data = $this->client->cancelSubscription($subscriptionId);

        $this->logger->info('stripe.cancelSubscription: response', ['data' => $data]);

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown error';
            $this->logger->error('stripe.cancelSubscription: api error', ['message' => $message]);
            throw SubscriptionException::apiError($message);
        }
    }

    /** @return array<int, Payment> */
    public function getSubscriptionPayments(string $subscriptionId): array
    {
        $this->logger->info('stripe.getSubscriptionPayments: request', ['subscriptionId' => $subscriptionId]);

        $data = $this->client->getSubscriptionPayments($subscriptionId);

        $this->logger->info('stripe.getSubscriptionPayments: response', ['count' => count($data['data'] ?? [])]);

        /** @var array<int, array<string, mixed>> $items */
        $items = $data['data'] ?? [];

        return array_map(
            fn (array $item) => $this->paymentMapper->toPaymentFromInvoice($item),
            $items,
        );
    }

    // --- Credit Card ---

    public function tokenizeCreditCard(string $customerId, CreditCardData $cardData): CreditCardToken
    {
        $payload = [
            'type'                  => 'card',
            'card[number]'          => $cardData->number,
            'card[exp_month]'       => $cardData->expiryMonth,
            'card[exp_year]'        => $cardData->expiryYear,
            'card[cvc]'             => $cardData->ccv,
            'billing_details[name]' => $cardData->holderName,
        ];

        $this->logger->info('stripe.tokenizeCreditCard: request', ['customerId' => $customerId]);

        $data = $this->client->createPaymentMethod($payload);

        $this->logger->info('stripe.tokenizeCreditCard: response', ['data' => $data]);

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown error';
            $this->logger->error('stripe.tokenizeCreditCard: api error', ['message' => $message]);
            throw PaymentException::apiError($message);
        }

        if (empty($data['id'])) {
            $this->logger->error('stripe.tokenizeCreditCard: unexpected empty response', ['data' => $data]);
            throw PaymentException::apiError('Unexpected empty response from Stripe API.');
        }

        // Attach payment method to customer
        $attachPayload = ['customer' => $customerId];
        $this->client->attachPaymentMethod($data['id'], $attachPayload);

        return $this->creditCardMapper->toToken($data);
    }
}
