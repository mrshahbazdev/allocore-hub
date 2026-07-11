@extends('layouts.app')

@section('title', $invoice->isEstimate() ? __('View Estimate') : __('View Invoice'))
@section('page-title', $invoice->isEstimate() ? __('Estimate') : __('Invoice').' '.$invoice->invoice_number)

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-semibold">
            {{ $invoice->isEstimate() ? __('Estimate') : __('Invoice') }} {{ $invoice->invoice_number }}
        </h2>
        <p class="text-sm text-gray-400">
            {{ $invoice->invoice_date->format('M d, Y') }} — {{ __('Due') }} {{ $invoice->due_date->format('M d, Y') }}
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($invoice->status === 'draft')
            <button wire:click="markAsSent" class="btn btn-primary">{{ __('Mark as Sent') }}</button>
        @endif
        @if(in_array($invoice->status, ['sent', 'overdue']))
            <button wire:click="markAsPaid" class="btn btn-success">{{ __('Mark as Paid') }}</button>
        @endif
        @if($invoice->status === 'sent' && $invoice->due_date->isPast())
            <button wire:click="markAsOverdue" class="btn btn-secondary">{{ __('Mark as Overdue') }}</button>
        @endif
        @if(! in_array($invoice->status, ['paid', 'cancelled']))
            <button wire:click="cancelInvoice" wire:confirm="{{ __('Are you sure you want to cancel this invoice?') }}" class="btn btn-secondary">{{ __('Cancel') }}</button>
        @endif
        <a href="{{ route('invoice.pdf', $invoice) }}" class="btn btn-secondary">{{ __('Download PDF') }}</a>
        @if($invoice->status === 'draft')
            <a href="{{ route('invoice.edit', $invoice) }}" class="btn btn-secondary">{{ __('Edit') }}</a>
        @endif
        <button wire:click="delete" wire:confirm="{{ __('Are you sure you want to delete this invoice?') }}" class="btn btn-danger">{{ __('Delete') }}</button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('From') }}</h4>
                    <p class="font-semibold">{{ $invoice->business->name }}</p>
                    @if($invoice->business->email)
                        <p class="text-sm text-gray-400">{{ $invoice->business->email }}</p>
                    @endif
                    @if($invoice->business->address)
                        <p class="text-sm text-gray-400 whitespace-pre-line">{{ $invoice->business->address }}</p>
                    @endif
                    @if($invoice->business->tax_number)
                        <p class="text-sm text-gray-400">{{ __('Tax ID') }}: {{ $invoice->business->tax_number }}</p>
                    @endif
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('To') }}</h4>
                    <p class="font-semibold">{{ $invoice->client->name }}</p>
                    @if($invoice->client->company_name)
                        <p class="text-sm text-gray-400">{{ $invoice->client->company_name }}</p>
                    @endif
                    @if($invoice->client->email)
                        <p class="text-sm text-gray-400">{{ $invoice->client->email }}</p>
                    @endif
                    @if($invoice->client->address)
                        <p class="text-sm text-gray-400 whitespace-pre-line">{{ $invoice->client->address }}</p>
                    @endif
                    @if($invoice->client->tax_number)
                        <p class="text-sm text-gray-400">{{ __('Tax ID') }}: {{ $invoice->client->tax_number }}</p>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th class="text-right">{{ __('Quantity') }}</th>
                            <th class="text-right">{{ __('Price') }}</th>
                            <th class="text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>
                                    <p class="font-medium">{{ $item->description }}</p>
                                    @if($item->tax_rate > 0)
                                        <p class="text-xs text-gray-400">{{ __('Tax') }}: {{ $item->tax_rate }}%</p>
                                    @endif
                                </td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td class="text-right">{{ $invoice->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right">{{ $invoice->currency_symbol }}{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-6">
                <div class="w-full md:w-1/2 lg:w-1/3">
                    <div class="flex justify-between py-2 border-b border-slate-700">
                        <span class="text-gray-400">{{ __('Subtotal') }}</span>
                        <span class="font-medium">{{ $invoice->currency_symbol }}{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-700">
                        <span class="text-gray-400">{{ __('Tax') }}</span>
                        <span class="font-medium">{{ $invoice->currency_symbol }}{{ number_format($invoice->tax_total, 2) }}</span>
                    </div>
                    @if($invoice->discount > 0)
                        <div class="flex justify-between py-2 border-b border-slate-700">
                            <span class="text-gray-400">{{ __('Discount') }}</span>
                            <span class="font-medium text-red-400">-{{ $invoice->currency_symbol }}{{ number_format($invoice->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between py-2 border-b border-slate-700">
                        <span class="text-gray-400">{{ __('Total') }}</span>
                        <span class="font-bold text-lg">{{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-700">
                        <span class="text-gray-400">{{ __('Paid') }}</span>
                        <span class="font-medium text-green-400">{{ $invoice->currency_symbol }}{{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-400">{{ __('Due') }}</span>
                        <span class="font-bold">{{ $invoice->currency_symbol }}{{ number_format($invoice->amount_due, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($invoice->notes)
                <div class="mt-6 p-4 rounded-lg" style="background: rgba(255,255,255,0.03);">
                    <h4 class="text-sm font-semibold mb-2">{{ __('Notes') }}</h4>
                    <p class="text-sm text-gray-400 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif

            @if($invoice->payment_terms)
                <div class="mt-4 p-4 rounded-lg" style="background: rgba(255,255,255,0.03);">
                    <h4 class="text-sm font-semibold mb-2">{{ __('Payment Terms') }}</h4>
                    <p class="text-sm text-gray-400 whitespace-pre-line">{{ $invoice->payment_terms }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <h4 class="card-title">{{ __('Status') }}</h4>
            <p class="text-lg font-medium mb-4">
                @if($invoice->status === 'paid')
                    <span class="badge badge-green">{{ __('Paid') }}</span>
                @elseif($invoice->status === 'sent')
                    <span class="badge badge-yellow">{{ __('Sent') }}</span>
                @elseif($invoice->status === 'overdue')
                    <span class="badge badge-red">{{ __('Overdue') }}</span>
                @elseif($invoice->status === 'cancelled')
                    <span class="badge badge-red">{{ __('Cancelled') }}</span>
                @else
                    <span class="badge badge-gray">{{ __('Draft') }}</span>
                @endif
            </p>

            @if($invoice->amount_due > 0)
                <h4 class="card-title">{{ __('Record Payment') }}</h4>
                <form wire:submit="recordPayment" class="space-y-3">
                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('Amount') }}</label>
                        <input type="number" step="0.01" wire:model="payment_amount" class="form-control">
                        @error('payment_amount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('Method') }}</label>
                        <select wire:model="payment_method" class="form-control">
                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                            <option value="credit_card">{{ __('Credit Card') }}</option>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="check">{{ __('Check') }}</option>
                            <option value="paypal">{{ __('PayPal') }}</option>
                            <option value="stripe">{{ __('Stripe') }}</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('Date') }}</label>
                        <input type="date" wire:model="payment_date" class="form-control">
                        @error('payment_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea wire:model="payment_notes" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">{{ __('Record Payment') }}</button>
                </form>
            @endif
        </div>

        @if($invoice->payments->count() > 0)
            <div class="card">
                <h4 class="card-title">{{ __('Payments') }}</h4>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Method') }}</th>
                                <th class="text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                                <tr>
                                    <td>{{ $payment->date->format('M d, Y') }}</td>
                                    <td>{{ $payment->method }}</td>
                                    <td class="text-right">{{ $invoice->currency_symbol }}{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
