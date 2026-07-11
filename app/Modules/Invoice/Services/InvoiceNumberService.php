<?php

namespace App\Modules\Invoice\Services;

use App\Modules\Invoice\Models\Business;
use App\Modules\Invoice\Models\Invoice;

class InvoiceNumberService
{
    public function generate(Business $business, string $type = 'invoice'): string
    {
        if ($type === 'estimate') {
            $prefix = $business->estimate_number_prefix ?? 'EST';
            $next = $business->estimate_number_next ?? 1;
            $updateField = 'estimate_number_next';
        } else {
            $prefix = $business->invoice_number_prefix ?? 'INV';
            $next = $business->invoice_number_next ?? 1;
            $updateField = 'invoice_number_next';
        }

        $unique = false;
        $number = '';

        while (! $unique) {
            $number = sprintf('%s-%04d', $prefix, $next);

            $exists = Invoice::where('business_id', $business->id)
                ->where('invoice_number', $number)
                ->exists();

            if (! $exists) {
                $unique = true;
            } else {
                $next++;
            }
        }

        $business->update([$updateField => $next + 1]);

        return $number;
    }
}
