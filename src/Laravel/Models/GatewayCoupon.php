<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GatewayCoupon extends Model
{
    protected $table = 'gateway_coupons';

    protected $fillable = [
        'code',
        'gateway',
        'gateway_coupon_id',
        'type',
        'value',
        'currency',
        'max_uses',
        'current_uses',
        'valid_from',
        'valid_until',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'type'       => 'string',
        'value'      => 'decimal:2',
        'max_uses'   => 'integer',
        'current_uses' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active'  => 'boolean',
        'metadata'   => 'array',
    ];

    /**
     * Relacionamento com subscrições que usam este cupom
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            GatewaySubscription::class,
            'gateway_subscription_coupons',
            'coupon_id',
            'subscription_id'
        )
            ->withTimestamps();
    }

    /**
     * Verifica se o cupom ainda é válido para uso
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now < $this->valid_from) {
            return false;
        }

        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Verifica se o cupom é percentual
     */
    public function isPercentage(): bool
    {
        return $this->type === 'percentage';
    }

    /**
     * Verifica se o cupom é valor fixo
     */
    public function isFixedAmount(): bool
    {
        return $this->type === 'fixed_amount';
    }

    /**
     * Incrementa o número de usos
     */
    public function incrementUsage(): void
    {
        $this->increment('current_uses');
    }

    /**
     * Obtém a representação em string
     */
    public function __toString(): string
    {
        if ($this->type === 'percentage') {
            return "{$this->code} ({$this->value}%)";
        }

        return "{$this->code} (-{$this->value})";
    }
}

