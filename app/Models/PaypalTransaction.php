<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaypalTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'paypal_order_id',
        'payer_email',
        'payer_name',
        'amount',
        'currency',
        'status',
        'description',
        'paypal_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paypal_response' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
