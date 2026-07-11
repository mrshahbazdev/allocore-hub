<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'weight' => 'float',
        'is_required' => 'boolean',
        'depends_on_question_id' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'template_id');
    }

    public function pillar(): BelongsTo
    {
        return $this->belongsTo(AuditPillar::class, 'pillar_id');
    }
}
