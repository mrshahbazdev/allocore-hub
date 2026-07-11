<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'billing_interval',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'plan_tool')
            ->withPivot('included', 'price_override', 'max_quantity');
    }

    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Bundle::class, 'plan_bundle')
            ->withPivot('price_override');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
