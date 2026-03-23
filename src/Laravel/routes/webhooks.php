<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\AsaasWebhookController;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\StripeWebhookController;

Route::post('/webhooks/asaas', AsaasWebhookController::class)
    ->name('webhooks.asaas');

Route::post('/webhooks/stripe', StripeWebhookController::class)
    ->name('webhooks.stripe');
