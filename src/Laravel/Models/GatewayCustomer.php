<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatewayCustomer extends Model
{
    protected $table = 'gateway_customers';

    protected $fillable = [
        'user_id',
        'gateway',
        'gateway_customer_id',
        'name',
        'email',
        'document',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(GatewaySubscription::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GatewayPayment::class, 'user_id', 'user_id')
            ->where('gateway', $this->gateway);
    }
}
