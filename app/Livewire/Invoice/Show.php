<?php

namespace App\Livewire\Invoice;

use App\Modules\Invoice\Models\Invoice;
use App\Modules\Invoice\Models\Payment;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    public string $payment_amount = '';

    public string $payment_method = 'bank_transfer';

    public string $payment_date = '';

    public string $payment_notes = '';

    public function mount($invoice): void
    {
        $this->invoice = Invoice::where('company_id', auth()->user()->currentCompany()?->id)
            ->with(['items.product', 'payments', 'client', 'business', 'template'])
            ->findOrFail($invoice);

        $this->payment_date = now()->toDateString();
    }

    public function markAsSent(): void
    {
        $this->invoice->update(['status' => Invoice::STATUS_SENT]);
        $this->invoice->deductInventory();

        session()->flash('success', __('Invoice marked as sent.'));
    }

    public function markAsPaid(): void
    {
        $this->invoice->update([
            'status' => Invoice::STATUS_PAID,
            'amount_paid' => $this->invoice->grand_total,
            'amount_due' => 0,
        ]);

        $this->invoice->payments()->create([
            'amount' => $this->invoice->grand_total,
            'method' => 'bank_transfer',
            'date' => now()->toDateString(),
            'notes' => __('Full payment'),
        ]);

        session()->flash('success', __('Invoice marked as paid.'));
    }

    public function markAsOverdue(): void
    {
        $this->invoice->update(['status' => Invoice::STATUS_OVERDUE]);

        session()->flash('success', __('Invoice marked as overdue.'));
    }

    public function cancelInvoice(): void
    {
        $this->invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        session()->flash('success', __('Invoice cancelled.'));
    }

    public function recordPayment(): void
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'payment_notes' => 'nullable|string',
        ]);

        $amount = (float) $this->payment_amount;

        if ($amount > $this->invoice->amount_due) {
            session()->flash('error', __('Payment amount cannot exceed the amount due.'));

            return;
        }

        Payment::create([
            'invoice_id' => $this->invoice->id,
            'amount' => $amount,
            'method' => $this->payment_method,
            'date' => $this->payment_date,
            'notes' => $this->payment_notes,
        ]);

        $totalPaid = $this->invoice->payments->sum('amount') + $amount;
        $this->invoice->update([
            'amount_paid' => $totalPaid,
            'amount_due' => $this->invoice->grand_total - $totalPaid,
        ]);

        if ($this->invoice->amount_due <= 0) {
            $this->invoice->update(['status' => Invoice::STATUS_PAID]);
        }

        $this->invoice->load('payments');
        $this->reset(['payment_amount', 'payment_method', 'payment_notes']);
        $this->payment_date = now()->toDateString();

        session()->flash('success', __('Payment recorded successfully.'));
    }

    public function delete(): void
    {
        $this->invoice->delete();

        session()->flash('success', __('Invoice deleted successfully.'));
        $this->redirect(route('invoice.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.invoice.show');
    }
}
