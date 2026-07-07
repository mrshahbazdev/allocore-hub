<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiDefinition extends Model
{
    protected $fillable = [
        'name_de',
        'name_en',
        'description_de',
        'description_en',
        'formula',
        'unit',
        'target_value',
        'warning_threshold',
        'critical_threshold',
        'frequency',
        'direction',
        'category',
        'is_template',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:4',
        'warning_threshold' => 'decimal:4',
        'critical_threshold' => 'decimal:4',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'de' ? $this->name_de : $this->name_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'de' ? $this->description_de : $this->description_en;
    }

    public function values(): HasMany
    {
        return $this->hasMany(KpiValue::class);
    }

    public function latestValue()
    {
        return $this->hasOne(KpiValue::class)->latestOfMany('recorded_at');
    }

    public function monthlyTargets(): HasMany
    {
        return $this->hasMany(KpiMonthlyTarget::class);
    }
}
