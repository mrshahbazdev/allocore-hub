<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'email',
        'phone',
        'company_name',
        'position',
        'linkedin',
        'website',
        'source',
        'status',
        'priority',
        'industry',
        'budget',
        'notes',
        'transferred_to_leados',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'transferred_to_leados' => 'boolean',
            'transferred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function paypalTransactions(): HasMany
    {
        return $this->hasMany(PaypalTransaction::class);
    }
}
