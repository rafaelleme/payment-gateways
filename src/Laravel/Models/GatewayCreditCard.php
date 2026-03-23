<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayCreditCard extends Model
{
    protected $table = 'gateway_credit_cards';

    protected $fillable = [
        'user_id',
        'gateway',
        'gateway_card_id',
        'token',
        'brand',
        'last_four_digits',
        'holder_name',
        'expiry_month',
        'expiry_year',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
