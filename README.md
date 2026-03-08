# rafaelleme/payment-gateways

Framework-agnostic PHP library for payment gateway integration, following **Ports and Adapters (Hexagonal Architecture)** and **DDD** principles.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.1 |
| `guzzlehttp/guzzle` | ^7.0 |
| `psr/log` | ^3.0 |
| `illuminate/contracts` | ^10.0\|^11.0\|^12.0 |
| `illuminate/support` | ^10.0\|^11.0\|^12.0 |
| `illuminate/http` | ^10.0\|^11.0\|^12.0 |
| `illuminate/routing` | ^10.0\|^11.0\|^12.0 |

> When used inside a Laravel application, all `illuminate/*` packages come bundled — no extra installation needed.

---

## Installation

```bash
composer require rafaelleme/payment-gateways
```

---

## Architecture

```
src/
├── Core/
│   ├── Domain/
│   │   ├── Contracts/
│   │   │   └── GatewayContract.php         # Single port (payments + customers + subscriptions)
│   │   ├── Entities/
│   │   │   ├── Payment.php
│   │   │   ├── Customer.php
│   │   │   └── Subscription.php
│   │   ├── ValueObjects/
│   │   │   ├── Money.php
│   │   │   ├── CustomerId.php
│   │   │   ├── CreditCard.php
│   │   │   ├── CreditCardData.php
│   │   │   ├── CreditCardHolderInfo.php
│   │   │   └── CreditCardToken.php
│   │   ├── Enums/
│   │   │   ├── BillingType.php
│   │   │   ├── PaymentStatus.php
│   │   │   ├── SubscriptionCycle.php
│   │   │   └── SubscriptionStatus.php
│   │   └── Exceptions/
│   │       ├── PaymentException.php
│   │       ├── CustomerException.php
│   │       └── SubscriptionException.php
│   └── Application/
│       └── Services/
│           ├── PaymentService.php
│           ├── CustomerService.php
│           └── SubscriptionService.php
├── Infrastructure/
│   ├── Gateways/
│   │   ├── Asaas/
│   │   │   ├── AsaasGateway.php            # Single adapter — implements GatewayContract
│   │   │   ├── AsaasClient.php             # HTTP communication
│   │   │   ├── Payments/
│   │   │   │   └── AsaasPaymentMapper.php
│   │   │   ├── Customers/
│   │   │   │   └── AsaasCustomerMapper.php
│   │   │   ├── Subscriptions/
│   │   │   │   └── AsaasSubscriptionMapper.php
│   │   │   └── CreditCard/
│   │   │       └── AsaasCreditCardMapper.php
│   │   └── FakeGateway.php                 # In-memory implementation for tests
│   └── Http/
│       └── GuzzleHttpClient.php
├── Support/
│   └── GatewayManager.php                  # Driver registry with lazy loading
└── Laravel/
    ├── PaymentGatewaysServiceProvider.php
    ├── Facades/
    │   └── PaymentGateway.php
    ├── Webhooks/
    │   ├── AsaasWebhookController.php
    │   ├── AsaasWebhookHandler.php
    │   └── Events/
    │       ├── PaymentReceived.php
    │       ├── PaymentOverdue.php
    │       └── PaymentRefused.php
    └── routes/
        └── webhooks.php
```

> **No DTOs.** Gateways receive and return domain entities directly.

---

## Laravel Integration

The package auto-discovers the `ServiceProvider` via `composer.json` `extra.laravel`.

### 1. Publish the config

```bash
php artisan vendor:publish --tag=payment-gateways-config
```

### 2. Configure `.env`

```dotenv
PAYMENT_GATEWAY_DEFAULT=asaas

ASAAS_API_KEY=your-api-key

# Production
ASAAS_BASE_URL=https://api.asaas.com

# Sandbox
# ASAAS_BASE_URL=https://sandbox.asaas.com
```

> The consuming project controls the URL — no sandbox flag in the library.

### 3. Use via Facade

```php
use Rafaelleme\PaymentGateways\Laravel\Facades\PaymentGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Customer;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Subscription;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\Enums\SubscriptionCycle;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;

// --- Customers ---
$customer = PaymentGateway::createCustomer(new Customer(
    name:    'John Doe',
    email:   'john@example.com',
    cpfCnpj: '12345678900',
));

// --- Payments ---
$payment = PaymentGateway::createPayment(new Payment(
    customerId:  new CustomerId($customer->id),
    value:       new Money(149.90),
    billingType: BillingType::PIX,
    dueDate:     '2026-05-01',
));

// --- Subscriptions ---
$subscription = PaymentGateway::createSubscription(new Subscription(
    customerId:  new CustomerId($customer->id),
    value:       new Money(29.90),
    billingType: BillingType::CREDIT_CARD,
    cycle:       SubscriptionCycle::MONTHLY,
    nextDueDate: '2026-05-01',
));

// --- Switch driver at runtime ---
PaymentGateway::driver('stripe')->createPayment($payment);
```

### 4. Use via Dependency Injection

```php
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\GatewayContract;

class PaymentController extends Controller
{
    public function __construct(private GatewayContract $gateway) {}
}
```

---

## Credit Card

### With token

```php
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCard;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardHolderInfo;

$subscription = new Subscription(
    // ...
    creditCard: new CreditCard(
        holderInfo: new CreditCardHolderInfo(
            name:          'John Doe',
            email:         'john@example.com',
            cpfCnpj:       '12345678900',
            postalCode:    '01310-100',
            addressNumber: '100',
        ),
        token: 'tok_abc123',
    ),
);
```

### With raw card data

```php
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardData;

$subscription = new Subscription(
    // ...
    creditCard: new CreditCard(
        holderInfo: new CreditCardHolderInfo(/* ... */),
        cardData: new CreditCardData(
            holderName:  'John Doe',
            number:      '4111111111111111',
            expiryMonth: '12',
            expiryYear:  '2030',
            ccv:         '123',
        ),
    ),
);
```

### Tokenize a card (sandbox/test)

```php
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CreditCardData;

$token = PaymentGateway::tokenizeCreditCard('cus_abc123', new CreditCardData(
    holderName:  'John Doe',
    number:      '4111111111111111',
    expiryMonth: '12',
    expiryYear:  '2030',
    ccv:         '123',
));

$token->token;       // 8608b88a-f74f-4f22-b3a1-dbbfc4c42cc9
$token->brand;       // VISA
$token->last4Digits; // 1111
```

---

## Webhooks

The package registers `POST /webhooks/asaas` automatically via the `ServiceProvider`.

> ⚠️ Add `/webhooks/asaas` to the `$except` list in your application's `VerifyCsrfToken` middleware.

### Asaas events mapped to Laravel events

| Asaas event | Laravel event dispatched |
|---|---|
| `PAYMENT_RECEIVED` | `PaymentReceived` |
| `PAYMENT_CONFIRMED` | `PaymentReceived` |
| `PAYMENT_OVERDUE` | `PaymentOverdue` |
| `PAYMENT_REFUSED` | `PaymentRefused` |

Unknown events are silently ignored.

### Register listeners in your application

```php
// app/Providers/EventServiceProvider.php
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentReceived;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentOverdue;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\Events\PaymentRefused;

protected $listen = [
    PaymentReceived::class => [HandlePaymentReceived::class],
    PaymentOverdue::class  => [HandlePaymentOverdue::class],
    PaymentRefused::class  => [HandlePaymentRefused::class],
];
```

### Access payment data inside a listener

```php
public function handle(PaymentReceived $event): void
{
    $paymentId     = $event->payment['id'];           // pay_xxx
    $subscriptionId = $event->payment['subscription']; // sub_xxx
    $value         = $event->payment['value'];         // 49.90
    $status        = $event->payment['status'];        // RECEIVED
}
```

---

## Error Handling

Each domain context has its own typed exception:

```php
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\PaymentException;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\CustomerException;
use Rafaelleme\PaymentGateways\Core\Domain\Exceptions\SubscriptionException;

try {
    $payment = PaymentGateway::createPayment($payment);
} catch (PaymentException $e) {
    // API error or unexpected empty response
}

try {
    $customer = PaymentGateway::getCustomer('cus_missing');
} catch (CustomerException $e) {
    // Customer not found
}

try {
    PaymentGateway::cancelSubscription('sub_missing');
} catch (SubscriptionException $e) {
    // Subscription not found or API error
}
```

---

## Adding a New Gateway

1. Create `src/Infrastructure/Gateways/{Gateway}/`
2. Implement `{Gateway}Gateway.php implements GatewayContract`
3. Register in `GatewayManager`

```php
$manager->extend('stripe', fn () => new StripeGateway(apiKey: 'sk_live_...'));
```

That's it — no changes needed to the core or application layers.

---

## Framework-agnostic Usage

```php
use Rafaelleme\PaymentGateways\Support\GatewayManager;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasClient;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;

$manager = new GatewayManager('asaas');

$manager->register('asaas', fn () => new AsaasGateway(
    client: new AsaasClient(
        apiKey:  'your-api-key',
        baseUrl: 'https://api.asaas.com',
    ),
));

$gateway = $manager->driver();
```

---

## Testing

Use `FakeGateway` to test without hitting external APIs:

```php
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\FakeGateway;

$gateway = new FakeGateway();

$customer = $gateway->createCustomer(new Customer(name: 'John', email: 'j@j.com'));
$payment  = $gateway->createPayment(new Payment(/* ... */));

$gateway->hasPayment($payment->id);   // true
$gateway->hasCustomer($customer->id); // true
$gateway->reset();                    // clear state between tests
```

---

## Running Tests

```bash
docker compose run --rm php ./vendor/bin/phpunit
docker compose run --rm php ./vendor/bin/phpstan analyse
docker compose run --rm php ./vendor/bin/php-cs-fixer fix
```

---

## License

MIT
