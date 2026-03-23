<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used when
    | no specific driver is requested.
    |
    */

    'default' => env('PAYMENT_GATEWAY_DEFAULT', 'asaas'),

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days after a failed or overdue payment before the subscription
    | is automatically cancelled. The consuming application schedules the job.
    |
    */

    'grace_period_days' => (int) env('PAYMENT_GATEWAY_GRACE_PERIOD_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the payment gateways used by your
    | application. The base_url is fully controlled by the consuming project —
    | use the sandbox or production URL as needed via environment variable.
    |
    | Supported drivers: "asaas", "stripe"
    |
    | Asaas URLs:
    |   Production: https://api.asaas.com
    |   Sandbox:    https://api-sandbox.asaas.com
    |
    | Stripe URLs:
    |   Production: https://api.stripe.com
    |   Sandbox:    https://api.stripe.com (same URL with test keys)
    |
    */

    'gateways' => [
        'asaas' => [
            'driver'   => 'asaas',
            'api_key'  => env('ASAAS_API_KEY', ''),
            'base_url' => env('ASAAS_BASE_URL', 'https://api.asaas.com'),
        ],
        'stripe' => [
            'driver'   => 'stripe',
            'api_key'  => env('STRIPE_SECRET_KEY', ''),
            'base_url' => env('STRIPE_BASE_URL', 'https://api.stripe.com'),
        ],
    ],
];
