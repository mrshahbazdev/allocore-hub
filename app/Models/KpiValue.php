<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiValue extends Model
{
    protected $fillable = [
        'kpi_definition_id',
        'value',
        'recorded_at',
        'notes',
        'recorded_by',
        'status',
        'source',
        'external_ref',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'recorded_at' => 'date',
    ];

    public function kpiDefinition(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
