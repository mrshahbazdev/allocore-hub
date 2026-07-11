<?php

namespace App\Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price',
        'purchase_price',
        'stock_quantity',
        'manage_stock',
        'unit',
        'tax_rate',
        'tax_percentage',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'manage_stock' => 'boolean',
        'stock_quantity' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function hasStock(int|float $quantity): bool
    {
        if (! $this->manage_stock) {
            return true;
        }

        return $this->stock_quantity >= $quantity;
    }

    public function decrementStock(int|float $quantity): void
    {
        if (! $this->manage_stock) {
            return;
        }

        $this->decrement('stock_quantity', $quantity);
    }
}
