<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImmobilienInput extends Model
{
    protected $fillable = [
        'analysis_id',
        'purchase_price', 'closing_costs', 'renovation_costs',
        'area_sqm', 'property_type', 'location',
        'rent_net', 'market_rent', 'vacancy_rate', 'management_costs_pct',
        'equity', 'loan_rate', 'repayment_rate', 'loan_term_years',
        'location_score', 'condition_score', 'rent_growth_score',
        'custom_weights',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'closing_costs' => 'decimal:2',
        'renovation_costs' => 'decimal:2',
        'area_sqm' => 'decimal:2',
        'rent_net' => 'decimal:2',
        'market_rent' => 'decimal:2',
        'vacancy_rate' => 'decimal:2',
        'management_costs_pct' => 'decimal:2',
        'equity' => 'decimal:2',
        'loan_rate' => 'decimal:2',
        'repayment_rate' => 'decimal:2',
        'loan_term_years' => 'integer',
        'location_score' => 'integer',
        'condition_score' => 'integer',
        'rent_growth_score' => 'integer',
        'custom_weights' => 'array',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
