<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Rafaelleme\PaymentGateways\Support\GatewayManager;

/**
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment             createPayment(\Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment $payment)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment             getPayment(string $paymentId)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer            createCustomer(\Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer $customer)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer            getCustomer(string $customerId)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription        createSubscription(\Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription $subscription)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription        getSubscription(string $subscriptionId)
 * @method static void                                                                 cancelSubscription(string $subscriptionId)
 * @method static array<int, \Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment> getSubscriptionPayments(string $subscriptionId)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardToken         tokenizeCreditCard(string $customerId, \Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardData $cardData)
 * @method static \Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract    driver(?string $name = null)
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
