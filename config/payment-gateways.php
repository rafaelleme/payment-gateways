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
    | application.
    |
    | Supported drivers: "asaas"
    |
    */

    'gateways' => [

        'asaas' => [
            'driver'   => 'asaas',
            'api_key'  => env('ASAAS_API_KEY', ''),
            'base_url' => env('ASAAS_BASE_URL', 'https://api.asaas.com/v3'),
            'sandbox'  => env('ASAAS_SANDBOX', false),
        ],

    ],

];
