<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\CustomerException;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\PaymentException;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\Customers\AsaasCustomerMapper;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\Payments\AsaasPaymentMapper;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\Subscriptions\AsaasSubscriptionMapper;

class AsaasGateway implements GatewayContract
{
    private readonly AsaasPaymentMapper $paymentMapper;
    private readonly AsaasCustomerMapper $customerMapper;
    private readonly AsaasSubscriptionMapper $subscriptionMapper;
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly AsaasClient $client,
        ?LoggerInterface $logger = null,
    ) {
        $this->paymentMapper      = new AsaasPaymentMapper();
        $this->customerMapper     = new AsaasCustomerMapper();
        $this->subscriptionMapper = new AsaasSubscriptionMapper();
        $this->logger             = $logger ?? new NullLogger();
    }

    // --- Payments ---

    public function createPayment(Payment $payment): Payment
    {
        $payload = [
            'customer'          => $payment->customerId->getValue(),
            'billingType'       => $payment->billingType->value,
            'value'             => $payment->value->getAmount(),
            'dueDate'           => $payment->dueDate,
            'description'       => $payment->description,
            'externalReference' => $payment->externalReference,
        ];

        $this->logger->info('asaas.createPayment: request', ['payload' => $payload]);

        $data = $this->client->createPayment($payload);

        $this->logger->info('asaas.createPayment: response', ['data' => $data]);

        if (isset($data['errors'])) {
            /** @var array<int, array<string, string>> $errors */
            $errors  = $data['errors'];
            $message = $errors[0]['description'] ?? 'Unknown error';
            $this->logger->error('asaas.createPayment: api error', ['message' => $message]);
            throw PaymentException::apiError($message);
        }

        if (empty($data['id'])) {
            $this->logger->error('asaas.createPayment: unexpected empty response', ['data' => $data]);
            throw PaymentException::apiError('Unexpected empty response from Asaas API.');
        }

        return $this->paymentMapper->toPayment($data);
    }

    public function getPayment(string $paymentId): Payment
    {
        $this->logger->info('asaas.getPayment: request', ['paymentId' => $paymentId]);

        $data = $this->client->getPayment($paymentId);

        $this->logger->info('asaas.getPayment: response', ['data' => $data]);

        if (empty($data['id'])) {
            $this->logger->warning('asaas.getPayment: not found', ['paymentId' => $paymentId]);
            throw PaymentException::notFound($paymentId);
        }

        return $this->paymentMapper->toPayment($data);
    }

    // --- Customers ---

    public function createCustomer(Customer $customer): Customer
    {
        $payload = [
            'name'              => $customer->name,
            'email'             => $customer->email,
            'phone'             => $customer->phone,
            'cpfCnpj'           => $customer->cpfCnpj,
            'externalReference' => $customer->externalReference,
        ];

        $this->logger->info('asaas.createCustomer: request', ['payload' => $payload]);

        $data = $this->client->createCustomer($payload);

        $this->logger->info('asaas.createCustomer: response', ['data' => $data]);

        if (isset($data['errors'])) {
            /** @var array<int, array<string, string>> $errors */
            $errors  = $data['errors'];
            $message = $errors[0]['description'] ?? 'Unknown error';
            $this->logger->error('asaas.createCustomer: api error', ['message' => $message]);
            throw CustomerException::apiError($message);
        }

        if (empty($data['id'])) {
            $this->logger->error('asaas.createCustomer: unexpected empty response', ['data' => $data]);
            throw CustomerException::apiError('Unexpected empty response from Asaas API.');
        }

        return $this->customerMapper->toCustomer($data);
    }

    public function getCustomer(string $customerId): Customer
    {
        $this->logger->info('asaas.getCustomer: request', ['customerId' => $customerId]);

        $data = $this->client->getCustomer($customerId);

        $this->logger->info('asaas.getCustomer: response', ['data' => $data]);

        if (empty($data['id'])) {
            $this->logger->warning('asaas.getCustomer: not found', ['customerId' => $customerId]);
            throw CustomerException::notFound($customerId);
        }

        return $this->customerMapper->toCustomer($data);
    }

    // --- Subscriptions ---

    public function createSubscription(Subscription $subscription): Subscription
    {
        $payload = [
            'customer'          => $subscription->customerId->getValue(),
            'billingType'       => $subscription->billingType->value,
            'value'             => $subscription->value->getAmount(),
            'cycle'             => $subscription->cycle->value,
            'nextDueDate'       => $subscription->nextDueDate,
            'description'       => $subscription->description,
            'externalReference' => $subscription->externalReference,
        ];

        if ($subscription->creditCard !== null) {
            $payload['creditCardToken']      = $subscription->creditCard->token;
            $payload['creditCardHolderInfo'] = $subscription->creditCard->holderInfo->toArray();
        }

        $this->logger->info('asaas.createSubscription: request', ['payload' => $payload]);

        $data = $this->client->createSubscription($payload);

        $this->logger->info('asaas.createSubscription: response', ['data' => $data]);

        if (isset($data['errors'])) {
            /** @var array<int, array<string, string>> $errors */
            $errors  = $data['errors'];
            $message = $errors[0]['description'] ?? 'Unknown error';
            $this->logger->error('asaas.createSubscription: api error', ['message' => $message]);
            throw SubscriptionException::apiError($message);
        }

        if (empty($data['id'])) {
            $this->logger->error('asaas.createSubscription: unexpected empty response', ['data' => $data]);
            throw SubscriptionException::apiError('Unexpected empty response from Asaas API.');
        }

        return $this->subscriptionMapper->toSubscription($data);
    }

    public function getSubscription(string $subscriptionId): Subscription
    {
        $this->logger->info('asaas.getSubscription: request', ['subscriptionId' => $subscriptionId]);

        $data = $this->client->getSubscription($subscriptionId);

        $this->logger->info('asaas.getSubscription: response', ['data' => $data]);

        if (empty($data['id'])) {
            $this->logger->warning('asaas.getSubscription: not found', ['subscriptionId' => $subscriptionId]);
            throw SubscriptionException::subscriptionNotFound($subscriptionId);
        }

        return $this->subscriptionMapper->toSubscription($data);
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        $this->logger->info('asaas.cancelSubscription: request', ['subscriptionId' => $subscriptionId]);

        $data = $this->client->cancelSubscription($subscriptionId);

        $this->logger->info('asaas.cancelSubscription: response', ['data' => $data]);

        if (isset($data['errors'])) {
            /** @var array<int, array<string, string>> $errors */
            $errors  = $data['errors'];
            $message = $errors[0]['description'] ?? 'Unknown error';
            $this->logger->error('asaas.cancelSubscription: api error', ['message' => $message]);
            throw SubscriptionException::apiError($message);
        }
    }

    /** @return array<int, Payment> */
    public function getSubscriptionPayments(string $subscriptionId): array
    {
        $this->logger->info('asaas.getSubscriptionPayments: request', ['subscriptionId' => $subscriptionId]);

        $data = $this->client->getSubscriptionPayments($subscriptionId);

        $this->logger->info('asaas.getSubscriptionPayments: response', ['count' => count($data['data'] ?? [])]);

        /** @var array<int, array<string, mixed>> $items */
        $items = $data['data'] ?? [];

        return array_map(
            fn (array $item) => $this->paymentMapper->toPayment($item),
            $items,
        );
    }
}
