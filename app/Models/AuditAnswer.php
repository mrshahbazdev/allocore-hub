<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'selected_options' => 'array',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AuditQuestion::class);
    }
}
