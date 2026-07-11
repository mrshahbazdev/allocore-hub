<?php

namespace App\Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'is_default',
        'primary_color',
        'font_family',
        'logo_position',
        'header_style',
        'footer_message',
        'signature_path',
        'payment_terms',
        'show_tax',
        'show_discount',
        'enable_qr',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'show_tax' => 'boolean',
        'show_discount' => 'boolean',
        'enable_qr' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
