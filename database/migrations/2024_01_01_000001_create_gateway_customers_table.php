<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gateway_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('gateway');
            $table->string('gateway_customer_id');
            $table->string('name');
            $table->string('email');
            $table->string('document')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'gateway_customer_id']);
            $table->index(['gateway', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_customers');
    }
};
