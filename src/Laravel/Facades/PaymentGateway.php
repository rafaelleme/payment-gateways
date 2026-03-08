<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Rafaelleme\PaymentGateways\Support\GatewayManager;

/**
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment        createPayment(\Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment $payment)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment        getPayment(string $paymentId)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway driver(?string $name = null)
 *
 * @see GatewayManager
 */
class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GatewayManager::class;
    }
}
