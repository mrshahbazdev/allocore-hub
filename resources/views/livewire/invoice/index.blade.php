@extends('layouts.app')

@section('title', __('Invoices'))
@section('page-title', __('Invoices'))

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-semibold">{{ __('Invoices') }}</h2>
        <p class="text-sm text-gray-400">{{ __('Manage your invoices') }}</p>
    </div>
    <a href="{{ route('invoice.create') }}" class="btn btn-primary">
        + {{ __('Create Invoice') }}
    </a>
</div>

<div class="card">
    <div class="form-grid mb-4">
        <div class="form-group mb-0">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search invoices...') }}" class="form-control">
        </div>
        <div class="form-group mb-0">
            <select wire:model.live="status" class="form-control">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="sent">{{ __('Sent') }}</option>
                <option value="paid">{{ __('Paid') }}</option>
                <option value="overdue">{{ __('Overdue') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="cursor-pointer" wire:click="sortBy('invoice_number')">
                        {{ __('Invoice') }}
                        @if($sortBy === 'invoice_number')
                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th>{{ __('Client') }}</th>
                    <th class="cursor-pointer" wire:click="sortBy('invoice_date')">
                        {{ __('Date') }}
                        @if($sortBy === 'invoice_date')
                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('invoice.show', $invoice) }}" class="font-medium text-indigo-300 hover:text-indigo-200">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $invoice->client->name }}</td>
                        <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                        <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                        <td class="font-medium">{{ $invoice->currency_symbol }}{{ number_format($invoice->grand_total, 2) }}</td>
                        <td>
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
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2 flex-wrap">
                                <a href="{{ route('invoice.show', $invoice) }}" class="btn btn-sm btn-secondary">{{ __('View') }}</a>
                                @if($invoice->status === 'draft')
                                    <a href="{{ route('invoice.edit', $invoice) }}" class="btn btn-sm btn-secondary">{{ __('Edit') }}</a>
                                @endif
                                @if($invoice->status === 'draft')
                                    <button wire:click="markAsSent({{ $invoice->id }})" class="btn btn-sm btn-success">{{ __('Send') }}</button>
                                @endif
                                @if(in_array($invoice->status, ['sent', 'overdue']))
                                    <button wire:click="markAsPaid({{ $invoice->id }})" class="btn btn-sm btn-success">{{ __('Paid') }}</button>
                                @endif
                                @if($invoice->status === 'sent' && $invoice->due_date->isPast())
                                    <button wire:click="markAsOverdue({{ $invoice->id }})" class="btn btn-sm btn-secondary">{{ __('Overdue') }}</button>
                                @endif
                                @if(! in_array($invoice->status, ['paid', 'cancelled']))
                                    <button wire:click="cancel({{ $invoice->id }})" wire:confirm="{{ __('Are you sure you want to cancel this invoice?') }}" class="btn btn-sm btn-secondary">{{ __('Cancel') }}</button>
                                @endif
                                <button wire:click="delete({{ $invoice->id }})" wire:confirm="{{ __('Are you sure you want to delete this invoice?') }}" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400">
                            {{ __('No invoices found.') }}
                            <a href="{{ route('invoice.create') }}" class="text-indigo-300 hover:text-indigo-200">{{ __('Create your first invoice') }}</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
