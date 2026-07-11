<?php

namespace App\Livewire\Invoice;

use App\Modules\Invoice\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sortBy = 'invoice_date';

    public string $sortDirection = 'desc';

    protected const PER_PAGE = 10;

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function delete(int $id): void
    {
        $invoice = $this->companyInvoiceQuery()->findOrFail($id);
        $invoice->delete();

        session()->flash('success', __('Invoice deleted successfully.'));
    }

    public function markAsSent(int $id): void
    {
        $invoice = $this->companyInvoiceQuery()->findOrFail($id);
        $invoice->update(['status' => Invoice::STATUS_SENT]);
        $invoice->deductInventory();

        session()->flash('success', __('Invoice marked as sent.'));
    }

    public function markAsPaid(int $id): void
    {
        $invoice = $this->companyInvoiceQuery()->findOrFail($id);
        $invoice->update([
            'status' => Invoice::STATUS_PAID,
            'amount_paid' => $invoice->grand_total,
            'amount_due' => 0,
        ]);

        session()->flash('success', __('Invoice marked as paid.'));
    }

    public function markAsOverdue(int $id): void
    {
        $invoice = $this->companyInvoiceQuery()->findOrFail($id);
        $invoice->update(['status' => Invoice::STATUS_OVERDUE]);

        session()->flash('success', __('Invoice marked as overdue.'));
    }

    public function cancel(int $id): void
    {
        $invoice = $this->companyInvoiceQuery()->findOrFail($id);
        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        session()->flash('success', __('Invoice cancelled.'));
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    protected function companyInvoiceQuery()
    {
        $company = auth()->user()->currentCompany();

        if (! $company) {
            return Invoice::query()->whereNull('company_id');
        }

        return Invoice::where('company_id', $company->id);
    }

    protected function getBaseQuery()
    {
        $query = $this->companyInvoiceQuery()->with('client')->orderBy($this->sortBy, $this->sortDirection);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('client', function ($cq) {
                        $cq->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('company_name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function render()
    {
        $invoices = $this->getBaseQuery()->paginate(self::PER_PAGE);

        return view('livewire.invoice.index', compact('invoices'));
    }
}
