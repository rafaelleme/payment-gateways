<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Core\Domain\Events;

use DateTimeImmutable;

final class WebhookEvent
{
    public function __construct(
        public readonly WebhookEventType  $type,
        public readonly string            $paymentId,
        public readonly DateTimeImmutable $occurredAt,
        public readonly ?string           $gatewayEventId = null,
    ) {
    }

    /** @param array<string, mixed> $raw */
    public static function fromRaw(WebhookEventType $type, string $paymentId, array $raw = []): self
    {
        return new self(
            type:           $type,
            paymentId:      $paymentId,
            occurredAt:     new DateTimeImmutable(),
            gatewayEventId: isset($raw['id']) ? (string) $raw['id'] : null,
        );
    }
}
