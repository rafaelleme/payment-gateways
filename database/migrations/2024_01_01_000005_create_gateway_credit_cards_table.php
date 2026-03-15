<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gateway_credit_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('gateway');
            $table->string('gateway_card_id');
            $table->string('token');
            $table->string('brand');
            $table->string('last_four_digits', 4);
            $table->string('holder_name');
            $table->string('expiry_month', 2)->nullable();
            $table->string('expiry_year', 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'gateway_card_id']);
            $table->index(['gateway', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_credit_cards');
    }
};

