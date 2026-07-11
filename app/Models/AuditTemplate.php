<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditTemplate extends Model
{
    protected $guarded = [];

    public function pillars(): HasMany
    {
        return $this->hasMany(AuditPillar::class, 'template_id')->orderBy('order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AuditQuestion::class, 'template_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
