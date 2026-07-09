<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiDefinition extends Model
{
    protected $fillable = [
        'company_id',
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
        'source',
        'source_key',
        'is_connected',
        'scale_max',
        'is_template',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:4',
        'warning_threshold' => 'decimal:4',
        'critical_threshold' => 'decimal:4',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
        'is_connected' => 'boolean',
        'scale_max' => 'decimal:4',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Restrict to KPIs the given user may see: company owners/managers see all
     * of their company's KPIs; members see only KPIs assigned to them.
     */
    public function scopeVisibleTo($query, ?User $user)
    {
        if (! $user || ! $user->company_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('company_id', $user->company_id);

        if (! $user->canManageCompany()) {
            $assignedIds = $user->assignedKpis()->pluck('kpi_definitions.id');
            $query->whereIn('id', $assignedIds);
        }

        return $query;
    }

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
