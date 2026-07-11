<?php

namespace App\Modules\Invoice\Models;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_INVOICE = 'invoice';

    public const TYPE_ESTIMATE = 'estimate';

    public const FREQUENCY_WEEKLY = 'weekly';

    public const FREQUENCY_MONTHLY = 'monthly';

    public const FREQUENCY_QUARTERLY = 'quarterly';

    public const FREQUENCY_YEARLY = 'yearly';

    protected $fillable = [
        'company_id',
        'business_id',
        'client_id',
        'template_id',
        'invoice_number',
        'status',
        'type',
        'invoice_date',
        'due_date',
        'notes',
        'payment_terms',
        'currency',
        'subtotal',
        'tax_total',
        'discount',
        'grand_total',
        'amount_paid',
        'amount_due',
        'is_recurring',
        'recurring_frequency',
        'next_run_date',
        'last_run_date',
        'inventory_deducted',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'is_recurring' => 'boolean',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'inventory_deducted' => 'boolean',
    ];

    public function isEstimate(): bool
    {
        return $this->type === self::TYPE_ESTIMATE;
    }

    public function calculateNextRunDate(): ?Carbon
    {
        if (! $this->is_recurring || ! $this->recurring_frequency) {
            return null;
        }

        $baseDate = $this->next_run_date ?: $this->invoice_date;

        return match ($this->recurring_frequency) {
            self::FREQUENCY_WEEKLY => $baseDate->copy()->addWeek(),
            self::FREQUENCY_MONTHLY => $baseDate->copy()->addMonth(),
            self::FREQUENCY_QUARTERLY => $baseDate->copy()->addMonths(3),
            self::FREQUENCY_YEARLY => $baseDate->copy()->addYear(),
            default => null,
        };
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(InvoiceComment::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SENT => 'blue',
            self::STATUS_PAID => 'green',
            self::STATUS_OVERDUE => 'red',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    public function getCurrencySymbolAttribute(): string
    {
        $currency = $this->currency ?? $this->business?->currency;

        return match (strtoupper($currency)) {
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'PKR' => 'Rs',
            'CAD', 'AUD', 'USD' => '$',
            'AED' => 'د.إ',
            default => $currency.' ',
        };
    }

    public function deductInventory(): void
    {
        if ($this->inventory_deducted || $this->isEstimate()) {
            return;
        }

        foreach ($this->items as $item) {
            if ($item->product && $item->product->manage_stock) {
                $item->product->decrementStock((float) $item->quantity);
            }
        }

        $this->update(['inventory_deducted' => true]);
    }
}
