<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Rafaelleme\PaymentGateways\Laravel\Http\Controllers\CouponController;

Route::prefix('/coupons')->middleware('api')->group(function () {
    Route::get('/', [CouponController::class, 'index'])
        ->name('coupons.index');

    Route::post('/', [CouponController::class, 'store'])
        ->name('coupons.store');

    Route::get('/{id}', [CouponController::class, 'show'])
        ->name('coupons.show');

    Route::put('/{id}', [CouponController::class, 'update'])
        ->name('coupons.update');

    Route::patch('/{id}', [CouponController::class, 'update'])
        ->name('coupons.update');

    Route::delete('/{id}', [CouponController::class, 'destroy'])
        ->name('coupons.destroy');

    Route::get('/code/{code}', [CouponController::class, 'findByCode'])
        ->name('coupons.find-by-code');

    Route::get('/gateway/{gateway}/active', [CouponController::class, 'activeByGateway'])
        ->name('coupons.active-by-gateway');

    Route::post('/validate/{code}', [CouponController::class, 'validate'])
        ->name('coupons.validate');
});

