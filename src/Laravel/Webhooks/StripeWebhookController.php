<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeWebhookController
{
    public function __construct(
        private readonly StripeWebhookHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        $this->handler->handle($payload);

        return response('', Response::HTTP_OK);
    }
}
