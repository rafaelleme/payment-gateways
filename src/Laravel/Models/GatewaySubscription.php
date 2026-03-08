<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatewaySubscription extends Model
{
    protected $table = 'gateway_subscriptions';

    protected $fillable = [
        'user_id',
        'gateway',
        'customer_id',
        'gateway_subscription_id',
        'plan_identifier',
        'status',
        'billing_type',
        'value',
        'next_due_date',
        'metadata',
    ];

    protected $casts = [
        'metadata'      => 'array',
        'next_due_date' => 'date',
        'value'         => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(GatewayCustomer::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GatewayPayment::class, 'subscription_id');
    }
}
