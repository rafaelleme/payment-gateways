<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatewayPayment extends Model
{
    protected $table = 'gateway_payments';

    protected $fillable = [
        'user_id',
        'gateway',
        'subscription_id',
        'gateway_payment_id',
        'status',
        'billing_type',
        'value',
        'paid_at',
        'due_date',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at'  => 'datetime',
        'due_date' => 'date',
        'value'    => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(GatewaySubscription::class, 'subscription_id');
    }
}
