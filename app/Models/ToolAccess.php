<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ToolAccess extends Model
{
    /**
     * Catalogue of tools that can be connected to the hub.
     * Add new tools here as they gain an Allocore push integration.
     */
    public const CATALOG = [
        'audit' => 'AuditPro',
        'invoice' => 'InvoiceMaker',
        'easysop' => 'EasySOP',
    ];

    protected $fillable = [
        'company_id',
        'tool',
        'name',
        'base_url',
        'api_key',
        'enabled',
        'status',
        'last_synced_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
    ];

    public static function generateKey(): string
    {
        return 'alc_'.Str::random(48);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
