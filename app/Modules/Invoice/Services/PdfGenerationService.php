<?php

namespace App\Modules\Invoice\Services;

use App\Modules\Invoice\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PdfGenerationService
{
    public function generate(Invoice $invoice): string
    {
        $invoice->load(['client', 'business', 'items.product', 'template']);

        $locale = $invoice->client->language
            ?? $invoice->business->company?->locale
            ?? config('app.locale');
        App::setLocale($locale);

        $pdf = Pdf::loadView('invoice.pdf', [
            'invoice' => $invoice,
        ]);

        return $pdf->output();
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['client', 'business', 'items.product', 'template']);

        $locale = $invoice->client->language
            ?? $invoice->business->company?->locale
            ?? config('app.locale');
        App::setLocale($locale);

        $pdf = Pdf::loadView('invoice.pdf', [
            'invoice' => $invoice,
        ]);

        return $pdf->download(($invoice->isEstimate() ? __('Estimate') : __('Invoice')).'-'.$invoice->invoice_number.'.pdf');
    }
}
