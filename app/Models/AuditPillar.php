<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditPillar extends Model
{
    protected $guarded = [];

    protected $casts = [
        'target_score' => 'float',
        'order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'template_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AuditQuestion::class, 'pillar_id');
    }
}
