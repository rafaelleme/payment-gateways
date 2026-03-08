<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Rafaelleme\PaymentGateways\Laravel\Webhooks\AsaasWebhookController;

Route::post('/webhooks/asaas', AsaasWebhookController::class)
    ->name('webhooks.asaas');
