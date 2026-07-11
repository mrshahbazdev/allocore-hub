<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordQuestion extends Model
{
    protected $fillable = [
        'keyword_subtopic_id',
        'question',
        'answer',
        'sort_order',
    ];

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(KeywordSubtopic::class, 'keyword_subtopic_id');
    }
}
