<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Audit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $query) {
            if (auth()->check()) {
                $companyId = auth()->user()->currentCompany()?->id;

                if ($companyId) {
                    $query->where('audits.company_id', $companyId);
                }
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AuditAnswer::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(AuditResult::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'template_id');
    }
}
