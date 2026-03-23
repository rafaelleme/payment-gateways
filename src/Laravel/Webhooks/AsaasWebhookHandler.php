<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks;

use Illuminate\Contracts\Events\Dispatcher;
use Psr\Log\LoggerInterface;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasWebhookEvent;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

class AsaasWebhookHandler
{
    public function __construct(
        private Dispatcher $events,
        private LoggerInterface $logger,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function handle(array $payload): void
    {
        $this->logger->info('Asaas webhook received', [
            'payload' => $payload,
        ]);

        $event   = AsaasWebhookEvent::tryFrom((string) ($payload['event'] ?? ''));
        $payment = (array) ($payload['payment'] ?? []);

        if ($event === null) {
            $this->logger->warning('Asaas webhook: event type not recognized', [
                'event' => $payload['event'] ?? null,
            ]);

            return;
        }

        $this->logger->info('Processing Asaas webhook event', [
            'event' => $event->value,
        ]);

        $dispatchEvent = $event->toDispatchEvent();

        match ($dispatchEvent) {
            'received' => $this->dispatchAndLog(PaymentReceived::class, $payment),
            'overdue'  => $this->dispatchAndLog(PaymentOverdue::class, $payment),
            'refused'  => $this->dispatchAndLog(PaymentRefused::class, $payment),
            default    => $this->logger->info('Asaas webhook: event type not mapped', [
                'dispatch_event' => $dispatchEvent,
            ]),
        };
    }

    /**
     * @param class-string $eventClass
     * @param array<string, mixed> $data
     */
    private function dispatchAndLog(string $eventClass, array $data): void
    {
        $this->logger->info('Dispatching event', [
            'event_class' => $eventClass,
            'data'        => $data,
        ]);

        $this->events->dispatch(new $eventClass($data));
    }
}
