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
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the payment gateways used by your
    | application. The base_url is fully controlled by the consuming project —
    | use the sandbox or production URL as needed via environment variable.
    |
    | Supported drivers: "asaas"
    |
    | Asaas URLs:
    |   Production: https://api.asaas.com
    |   Sandbox:    https://api-sandbox.asaas.com
    |
    */

    'gateways' => [
        'asaas' => [
            'driver'   => 'asaas',
            'api_key'  => env('ASAAS_API_KEY', ''),
            'base_url' => env('ASAAS_BASE_URL', 'https://api.asaas.com'),
        ],
    ],
];
