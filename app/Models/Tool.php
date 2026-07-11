<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tool extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'internal_route',
        'namespace',
        'icon',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(Bundle::class, 'bundle_tool');
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_tool')
            ->withPivot('included', 'price_override', 'max_quantity');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_tool')
            ->withPivot('status', 'expires_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
