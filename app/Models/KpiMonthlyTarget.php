<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiMonthlyTarget extends Model
{
    protected $fillable = [
        'kpi_definition_id',
        'year',
        'month',
        'target_value',
        'growth_rate',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'target_value' => 'decimal:4',
        'growth_rate' => 'decimal:4',
    ];

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }
}
