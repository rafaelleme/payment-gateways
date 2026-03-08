<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gateway_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('gateway');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->foreign('subscription_id')->references('id')->on('gateway_subscriptions')->nullOnDelete();
            $table->string('gateway_payment_id');
            $table->string('status');
            $table->string('billing_type');
            $table->decimal('value', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->date('due_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'gateway_payment_id']);
            $table->index(['gateway', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_payments');
    }
};
