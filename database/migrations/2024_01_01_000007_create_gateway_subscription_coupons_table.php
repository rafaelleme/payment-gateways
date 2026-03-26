<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gateway_subscription_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->unsignedBigInteger('coupon_id')->index();
            $table->timestamps();

            $table->foreign('subscription_id')
                ->references('id')
                ->on('gateway_subscriptions')
                ->onDelete('cascade');

            $table->foreign('coupon_id')
                ->references('id')
                ->on('gateway_coupons')
                ->onDelete('cascade');

            $table->unique(['subscription_id', 'coupon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_subscription_coupons');
    }
};

