<?php

namespace App\Livewire\Invoice;

use App\Modules\Invoice\Models\Business;
use App\Modules\Invoice\Models\Client;
use App\Modules\Invoice\Models\Invoice;
use App\Modules\Invoice\Models\Product;
use App\Modules\Invoice\Services\InvoiceCalculationService;
use App\Modules\Invoice\Services\InvoiceNumberService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    public ?int $client_id = null;

    public ?int $template_id = null;

    public string $invoice_date = '';

    public string $due_date = '';

    public string $notes = '';

    public string $payment_terms = '';

    public string $discount = '0';

    public string $currency = 'EUR';

    public bool $is_recurring = false;

    public string $recurring_frequency = 'monthly';

    public string $next_run_date = '';

    public array $items = [];

    public string $product_search = '';

    public bool $creatingClient = false;

    public string $new_client_name = '';

    public string $new_client_email = '';

    public string $new_client_company_name = '';

    public string $new_client_address = '';

    protected InvoiceNumberService $invoiceNumberService;

    protected InvoiceCalculationService $calculationService;

    public function boot(InvoiceNumberService $invoiceNumberService, InvoiceCalculationService $calculationService): void
    {
        $this->invoiceNumberService = $invoiceNumberService;
        $this->calculationService = $calculationService;
    }

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
        $this->next_run_date = now()->toDateString();

        $business = $this->business;
        $this->currency = $business->currency;
        $this->template_id = $business->templates()->where('is_default', true)->first()?->id;
        $this->payment_terms = $business->payment_terms ?? $business->templates()->where('is_default', true)->first()?->payment_terms ?? '';
    }

    #[Computed]
    public function business(): Business
    {
        $company = auth()->user()->currentCompany();

        if (! $company) {
            abort(403, __('No company selected.'));
        }

        return Business::forCompany($company);
    }

    #[Computed]
    public function clients()
    {
        return $this->business->clients()->orderBy('name')->get();
    }

    #[Computed]
    public function templates()
    {
        return $this->business->templates()->orderBy('name')->get();
    }

    #[Computed]
    public function products()
    {
        if (empty($this->product_search)) {
            return collect();
        }

        return $this->business->products()
            ->where('name', 'like', '%'.$this->product_search.'%')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function totals()
    {
        return $this->calculationService->calculate($this->items, (float) $this->discount);
    }

    #[Computed]
    public function currency_symbol(): string
    {
        return $this->business->currency_symbol;
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount' => 0,
            'total' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function selectProduct(Product $product): void
    {
        $this->items[] = [
            'product_id' => $product->id,
            'description' => $product->name.($product->description ? ' - '.$product->description : ''),
            'quantity' => 1,
            'unit_price' => $product->price,
            'tax_rate' => $product->tax_rate,
            'tax_amount' => 0,
            'discount' => 0,
            'total' => 0,
        ];

        $this->updateItemTotal(count($this->items) - 1);
        $this->product_search = '';
    }

    public function updatedClientId($value): void
    {
        if ($value) {
            $client = Client::find($value);
            if ($client && $client->currency) {
                $this->currency = $client->currency;
            }
        }
    }

    public function updateItemTotal(int $index): void
    {
        $item = $this->items[$index];
        $total = $item['quantity'] * $item['unit_price'];
        $tax = $total * ($item['tax_rate'] / 100);
        $this->items[$index]['tax_amount'] = $tax;
        $this->items[$index]['total'] = $total + $tax - $item['discount'];
    }

    public function createClient(): void
    {
        $this->validate([
            'new_client_name' => 'required|string|max:255',
            'new_client_email' => 'nullable|email|max:255',
            'new_client_company_name' => 'nullable|string|max:255',
            'new_client_address' => 'nullable|string',
        ], [], [
            'new_client_name' => 'client name',
            'new_client_email' => 'client email',
            'new_client_company_name' => 'company name',
            'new_client_address' => 'client address',
        ]);

        $client = $this->business->clients()->create([
            'user_id' => auth()->id(),
            'name' => $this->new_client_name,
            'email' => $this->new_client_email,
            'company_name' => $this->new_client_company_name,
            'address' => $this->new_client_address,
            'currency' => $this->currency,
            'language' => app()->getLocale(),
        ]);

        $this->client_id = $client->id;
        $this->creatingClient = false;
        $this->reset(['new_client_name', 'new_client_email', 'new_client_company_name', 'new_client_address']);

        session()->flash('success', __('Client created successfully.'));
    }

    public function save(): void
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'required_if:is_recurring,true|nullable|string',
            'next_run_date' => 'required_if:is_recurring,true|nullable|date',
        ], [], [
            'client_id' => 'client',
            'items.*.description' => 'description',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
            'items.*.tax_rate' => 'tax rate',
            'recurring_frequency' => 'recurring frequency',
            'next_run_date' => 'next run date',
        ]);

        $company = auth()->user()->currentCompany();
        $business = $this->business;
        $totals = $this->totals;

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'business_id' => $business->id,
            'client_id' => $this->client_id,
            'template_id' => $this->template_id,
            'invoice_number' => $this->invoiceNumberService->generate($business),
            'status' => Invoice::STATUS_DRAFT,
            'type' => Invoice::TYPE_INVOICE,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
            'payment_terms' => $this->payment_terms,
            'currency' => $this->currency,
            'subtotal' => $totals['subtotal'],
            'tax_total' => $totals['tax_total'],
            'discount' => $totals['discount'],
            'grand_total' => $totals['grand_total'],
            'amount_paid' => 0,
            'amount_due' => $totals['grand_total'],
            'is_recurring' => $this->is_recurring,
            'recurring_frequency' => $this->is_recurring ? $this->recurring_frequency : null,
            'next_run_date' => $this->is_recurring ? $this->next_run_date : null,
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'discount' => $item['discount'],
                'total' => $item['total'],
            ]);
        }

        session()->flash('success', __('Invoice created successfully.'));
        $this->redirect(route('invoice.show', $invoice), navigate: true);
    }

    public function render()
    {
        return view('livewire.invoice.create');
    }
}
