<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bundle extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'discount_percent',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'bundle_tool');
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_bundle')
            ->withPivot('price_override');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
