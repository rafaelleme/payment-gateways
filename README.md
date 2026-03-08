# rafaelleme/payment-gateways

Framework-agnostic PHP library for payment gateway integration, following **Ports and Adapters (Hexagonal Architecture)** and **DDD** principles.

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
│   │   │   └── PaymentGateway.php       # Port (interface)
│   │   ├── Entities/
│   │   │   └── Payment.php              # Entity — input and output
│   │   └── ValueObjects/
│   │       ├── BillingType.php          # enum
│   │       ├── CustomerId.php
│   │       ├── Money.php
│   │       └── PaymentStatus.php        # enum
│   └── Application/
│       └── Services/
│           └── PaymentService.php
├── Infrastructure/
│   └── Gateways/
│       └── Asaas/
│           ├── AsaasGateway.php         # Adapter
│           └── AsaasPaymentMapper.php
├── Support/
│   └── GatewayManager.php
└── Laravel/
    ├── PaymentGatewaysServiceProvider.php
    └── Facades/
        └── PaymentGateway.php
```

> **No DTOs.** Gateways receive and return domain entities directly. If a result needs extra data, enrich the entity or model an Aggregate.

---

## Domain Design

### Payment entity

`Payment` is the central entity. It represents both the **creation command** and the **gateway response**:

| Property | Type | Nullable | Description |
|---|---|---|---|
| `customerId` | `CustomerId` | no | Customer identifier |
| `value` | `Money` | no | Payment amount |
| `billingType` | `BillingType` | no | Payment method |
| `dueDate` | `string` | no | Due date |
| `description` | `string` | yes | Optional description |
| `externalReference` | `string` | yes | Your internal reference |
| `id` | `string` | yes | Gateway ID (null before creation) |
| `status` | `PaymentStatus` | yes | Payment status (null before creation) |
| `invoiceUrl` | `string` | yes | Invoice URL (null before creation) |

```php
// Before creation — no id/status
$payment = new Payment(
    customerId:  new CustomerId('cus_abc123'),
    value:       new Money(150.00),
    billingType: BillingType::PIX,
    dueDate:     '2026-04-30',
);

$payment->isPersisted(); // false
$payment->isPaid();      // false

// After gateway returns — entity enriched
$result = $service->create($payment);

$result->id;             // 'pay_xyz'
$result->status;         // PaymentStatus::PENDING
$result->isPersisted();  // true
$result->isPaid();       // false
$result->toArray();      // array representation
```

---

## Usage (Framework-agnostic)

```php
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;
use Rafaelleme\PaymentGateways\Core\Application\Services\PaymentService;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;

$gateway = new AsaasGateway(
    apiKey:  'your-api-key',
    baseUrl: 'https://api.asaas.com/v3',
);

$service = new PaymentService($gateway);

$payment = new Payment(
    customerId:  new CustomerId('cus_abc123'),
    value:       new Money(150.00),
    billingType: BillingType::PIX,
    dueDate:     '2026-04-30',
    description: 'Order #1234',
);

$result = $service->create($payment); // returns Payment entity

echo $result->id;               // pay_xyz
echo $result->status->value;    // PENDING
echo $result->status->label();  // Aguardando Pagamento
echo $result->invoiceUrl;       // https://...
```

### Using the GatewayManager

```php
use Rafaelleme\PaymentGateways\Support\GatewayManager;
use Rafaelleme\PaymentGateways\Infrastructure\Gateways\Asaas\AsaasGateway;

$manager = new GatewayManager(defaultDriver: 'asaas');

$manager->register('asaas', fn () => new AsaasGateway(
    apiKey:  'your-api-key',
    baseUrl: 'https://api.asaas.com/v3',
));

$gateway = $manager->driver();         // default driver
$gateway = $manager->driver('asaas'); // explicit driver
```

---

## Laravel Integration

The package auto-discovers the ServiceProvider via `composer.json` `extra.laravel`.

### 1. Publish the config

```bash
php artisan vendor:publish --tag=payment-gateways-config
```

### 2. Configure `.env`

```dotenv
PAYMENT_GATEWAY_DEFAULT=asaas

ASAAS_API_KEY=your-api-key
ASAAS_BASE_URL=https://api.asaas.com/v3
ASAAS_SANDBOX=false
```

### 3. Use via Dependency Injection

```php
use Rafaelleme\PaymentGateways\Core\Domain\Contracts\PaymentGateway;
use Rafaelleme\PaymentGateways\Core\Domain\Entities\Payment;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\BillingType;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\CustomerId;
use Rafaelleme\PaymentGateways\Core\Domain\ValueObjects\Money;

class PaymentController extends Controller
{
    public function __construct(private PaymentGateway $gateway) {}

    public function store(Request $request)
    {
        $result = $this->gateway->createPayment(
            new Payment(
                customerId:  new CustomerId($request->customer_id),
                value:       new Money($request->amount),
                billingType: BillingType::from($request->billing_type),
                dueDate:     $request->due_date,
            )
        );

        return response()->json($result->toArray());
    }
}
```

### 4. Use via Facade

```php
use Rafaelleme\PaymentGateways\Laravel\Facades\PaymentGateway;

$result = PaymentGateway::createPayment($payment);

// Switch driver at runtime
$result = PaymentGateway::driver('asaas')->createPayment($payment);
```

---

## Adding a New Gateway

1. Create `src/Infrastructure/Gateways/{Gateway}/`
2. Create the mapper: `{Gateway}PaymentMapper.php` — maps API response to `Payment` entity
3. Implement the adapter: `{Gateway}Gateway.php implements PaymentGateway`
4. Register in `GatewayManager`

```php
$manager->extend('stripe', fn () => new StripeGateway(
    apiKey: 'sk_live_...',
));
```

---

## Value Objects

| Class | Type | Description |
|-------|------|-------------|
| `Money` | Class | Immutable. Amount + currency (default `BRL`) |
| `BillingType` | Enum | `BOLETO`, `PIX`, `CREDIT_CARD`, `DEBIT_CARD`, `TRANSFER`, `UNDEFINED` |
| `CustomerId` | Class | Immutable wrapper for customer ID string |
| `PaymentStatus` | Enum | `PENDING`, `CONFIRMED`, `RECEIVED`, `OVERDUE`, `REFUNDED`, `CANCELLED` |

---

## Running Tests

```bash
docker compose run --rm php ./vendor/bin/phpunit
```

---

## Requirements

- PHP 8.1+
- `guzzlehttp/guzzle` ^7.0
- For Laravel integration: `illuminate/support` ^9.0|^10.0|^11.0

---

## License

MIT
