<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Analysis extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'type', 'name', 'status',
        'total_score', 'recommendation', 'notes',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gmbhInput(): HasOne
    {
        return $this->hasOne(GmbhInput::class);
    }

    public function jahresabschlussInputs(): HasMany
    {
        return $this->hasMany(JahresabschlussInput::class)->orderBy('year_order');
    }

    public function immobilienInput(): HasOne
    {
        return $this->hasOne(ImmobilienInput::class);
    }

    public function kpiResults(): HasMany
    {
        return $this->hasMany(KpiResult::class);
    }

    // Score Color Helper
    public function scoreColor(): string
    {
        if ($this->total_score === null) {
            return 'gray';
        }
        if ($this->total_score >= 70) {
            return 'green';
        }
        if ($this->total_score >= 45) {
            return 'yellow';
        }

        return 'red';
    }

    // Type Label
    public function typeLabel(): string
    {
        return match ($this->type) {
            'gmbh' => 'GmbH Analyse',
            'jahresabschluss' => 'Jahresabschluss',
            'immobilien' => 'Immobilienanalyse',
            default => $this->type,
        };
    }
}
