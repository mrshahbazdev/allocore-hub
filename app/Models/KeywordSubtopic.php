<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeywordSubtopic extends Model
{
    protected $fillable = [
        'keyword_project_id',
        'title',
        'description',
        'long_tail_keyword',
        'search_volume',
        'cpc',
        'competition',
        'competition_index',
        'low_bid',
        'high_bid',
        'sort_order',
        'cluster_title',
        'cluster_content',
        'cluster_meta_description',
    ];

    protected $casts = [
        'search_volume' => 'integer',
        'cpc' => 'float',
        'competition_index' => 'integer',
        'low_bid' => 'float',
        'high_bid' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(KeywordProject::class, 'keyword_project_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(KeywordQuestion::class, 'keyword_subtopic_id')->orderBy('sort_order');
    }
}
