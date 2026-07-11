<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Tool;
use App\Models\User;
use App\Modules\Invoice\Models\Business;
use App\Modules\Invoice\Models\Client;
use App\Modules\Invoice\Models\Invoice;
use App\Modules\Invoice\Models\Product;
use App\Modules\Invoice\Services\InvoiceCalculationService;
use App\Modules\Invoice\Services\InvoiceNumberService;
use Illuminate\Database\Seeder;

class InvoiceModuleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@allocore.de')->first();

        if (! $admin) {
            return;
        }

        $company = Company::firstOrCreate(
            ['name' => 'Demo Company', 'user_id' => $admin->id],
            [
                'currency' => 'EUR',
                'country' => 'DE',
                'description' => 'Demo company for the invoice module.',
            ]
        );

        $company->users()->syncWithoutDetaching([
            $admin->id => [
                'role' => 'owner',
                'is_default' => true,
                'invited_at' => now(),
                'accepted_at' => now(),
            ],
        ]);

        $tool = Tool::where('slug', 'invoice')->first();

        if ($tool) {
            $company->tools()->syncWithoutDetaching([
                $tool->id => ['status' => 'active', 'expires_at' => null],
            ]);
        }

        $business = Business::forCompany($company);
        $business->update([
            'email' => 'billing@example.com',
            'tax_number' => 'DE123456789',
            'address' => "Demo Street 1\n12345 Berlin, Germany",
            'iban' => 'DE89370400440532013000',
            'bic' => 'COBADEFFXXX',
            'payment_terms' => 'Bitte zahlen Sie innerhalb von 14 Tagen.',
        ]);

        $client = Client::firstOrCreate(
            ['business_id' => $business->id, 'email' => 'client@example.com'],
            [
                'user_id' => $admin->id,
                'name' => 'Max Mustermann',
                'company_name' => 'Musterfirma GmbH',
                'address' => "Musterstrasse 2\n10115 Berlin, Germany",
                'currency' => 'EUR',
                'language' => 'de',
            ]
        );

        $product = Product::firstOrCreate(
            ['business_id' => $business->id, 'name' => 'Beratungsleistung'],
            [
                'description' => 'Stundenbasierte Beratungsleistung',
                'price' => 150,
                'unit' => 'Stunde',
                'tax_rate' => 19,
            ]
        );

        if ($business->invoices()->count() === 0) {
            $invoiceNumberService = new InvoiceNumberService;
            $calculationService = new InvoiceCalculationService;

            $items = [
                [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => 10,
                    'unit_price' => $product->price,
                    'tax_rate' => $product->tax_rate,
                    'discount' => 0,
                ],
            ];

            $totals = $calculationService->calculate($items, 0);

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'business_id' => $business->id,
                'client_id' => $client->id,
                'template_id' => $business->templates()->where('is_default', true)->first()?->id,
                'invoice_number' => $invoiceNumberService->generate($business),
                'status' => Invoice::STATUS_SENT,
                'type' => Invoice::TYPE_INVOICE,
                'invoice_date' => now(),
                'due_date' => now()->addDays(14),
                'notes' => 'Vielen Dank für Ihre Zusammenarbeit.',
                'payment_terms' => $business->payment_terms,
                'currency' => 'EUR',
                'subtotal' => $totals['subtotal'],
                'tax_total' => $totals['tax_total'],
                'discount' => $totals['discount'],
                'grand_total' => $totals['grand_total'],
                'amount_paid' => 0,
                'amount_due' => $totals['grand_total'],
                'is_recurring' => false,
            ]);

            foreach ($totals['items'] as $item) {
                $invoice->items()->create($item);
            }
        }
    }
}
