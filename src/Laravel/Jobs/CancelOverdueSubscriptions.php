<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\SubscriptionRepositoryContract;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;
use Rafaelleme\PaymentGateways\Laravel\Models\GatewaySubscription;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\SubscriptionCancelled;

class CancelOverdueSubscriptions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int    $gracePeriodDays,
        private readonly string $gateway,
    ) {
    }

    public function handle(
        GatewayContract $gatewayClient,
        SubscriptionRepositoryContract $repository,
        Dispatcher $events,
    ): void {
        $cutoff = now()->subDays($this->gracePeriodDays);

        $subscriptions = GatewaySubscription::where('gateway', $this->gateway)
            ->whereNotNull('failed_at')
            ->where('failed_at', '<=', $cutoff)
            ->whereNotIn('status', ['CANCELLED', 'INACTIVE'])
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $gatewayClient->cancelSubscription($subscription->gateway_subscription_id);
            } catch (SubscriptionException) {
                // Already cancelled on gateway side — proceed to update locally
            }

            $repository->updateStatus(
                $this->gateway,
                $subscription->gateway_subscription_id,
                'CANCELLED',
            );

            $events->dispatch(new SubscriptionCancelled(
                gatewaySubscriptionId: $subscription->gateway_subscription_id,
                gateway:               $this->gateway,
                userId:                $subscription->user_id,
            ));
        }
    }
}
