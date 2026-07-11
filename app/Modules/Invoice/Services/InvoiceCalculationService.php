<?php

namespace App\Modules\Invoice\Services;

class InvoiceCalculationService
{
    public function calculate(array $items, float $discount = 0): array
    {
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $itemTaxAmount = $itemTotal * ($item['tax_rate'] / 100);
            $item['total'] = $itemTotal + $itemTaxAmount - ($item['discount'] ?? 0);
            $item['tax_amount'] = $itemTaxAmount;

            $subtotal += $itemTotal;
            $taxTotal += $itemTaxAmount;
        }

        $grandTotal = $subtotal + $taxTotal - $discount;

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'discount' => round($discount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
