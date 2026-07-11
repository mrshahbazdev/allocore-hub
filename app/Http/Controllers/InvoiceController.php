<?php

namespace App\Http\Controllers;

use App\Modules\Invoice\Models\Invoice;
use App\Modules\Invoice\Services\PdfGenerationService;

class InvoiceController extends Controller
{
    protected PdfGenerationService $pdfService;

    public function __construct(PdfGenerationService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function pdf(int $invoice)
    {
        $company = auth()->user()->currentCompany();

        if (! $company) {
            abort(403, __('No company selected.'));
        }

        $invoice = Invoice::where('company_id', $company->id)->findOrFail($invoice);

        return $this->pdfService->download($invoice);
    }
}
