@extends('layouts.app')

@section('title', __('Create Invoice'))
@section('page-title', __('Create Invoice'))

@section('content')
<div class="card">
    <form wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label">{{ __('Client') }} *</label>
                <select wire:model.live="client_id" class="form-control">
                    <option value="">{{ __('Select a client...') }}</option>
                    @foreach($this->clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} @if($client->company_name) ({{ $client->company_name }}) @endif</option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror

                <button type="button" wire:click="$toggle('creatingClient')" class="text-sm text-indigo-300 mt-2 hover:text-indigo-200">
                    + {{ __('Create a new client') }}
                </button>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Template') }}</label>
                <select wire:model="template_id" class="form-control">
                    <option value="">{{ __('Select a template...') }}</option>
                    @foreach($this->templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                @error('template_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        @if($creatingClient)
            <div class="card mb-6" style="background: rgba(255,255,255,0.03);">
                <h4 class="card-title">{{ __('New Client') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">{{ __('Name') }} *</label>
                        <input type="text" wire:model="new_client_name" class="form-control">
                        @error('new_client_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" wire:model="new_client_email" class="form-control">
                        @error('new_client_email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Company Name') }}</label>
                        <input type="text" wire:model="new_client_company_name" class="form-control">
                        @error('new_client_company_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Address') }}</label>
                        <textarea wire:model="new_client_address" class="form-control" rows="2"></textarea>
                        @error('new_client_address') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <button type="button" wire:click="createClient" class="btn btn-primary mt-2">{{ __('Save Client') }}</button>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="form-group">
                <label class="form-label">{{ __('Invoice Date') }} *</label>
                <input type="date" wire:model="invoice_date" class="form-control">
                @error('invoice_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Due Date') }} *</label>
                <input type="date" wire:model="due_date" class="form-control">
                @error('due_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="form-group">
                <label class="form-label">{{ __('Currency') }}</label>
                <select wire:model="currency" class="form-control">
                    <option value="EUR">EUR - {{ __('Euro') }}</option>
                    <option value="USD">USD - {{ __('US Dollar') }}</option>
                    <option value="GBP">GBP - {{ __('British Pound') }}</option>
                    <option value="CAD">CAD - {{ __('Canadian Dollar') }}</option>
                    <option value="AUD">AUD - {{ __('Australian Dollar') }}</option>
                    <option value="JPY">JPY - {{ __('Japanese Yen') }}</option>
                    <option value="PKR">PKR - {{ __('Pakistani Rupee') }}</option>
                    <option value="INR">INR - {{ __('Indian Rupee') }}</option>
                    <option value="AED">AED - {{ __('UAE Dirham') }}</option>
                </select>
                @error('currency') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Discount') }}</label>
                <input type="number" step="0.01" wire:model.live="discount" class="form-control" placeholder="0.00">
                @error('discount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Payment Terms') }}</label>
                <textarea wire:model="payment_terms" class="form-control" rows="2"></textarea>
                @error('payment_terms') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="form-group mt-4">
            <label class="form-label">{{ __('Notes') }}</label>
            <textarea wire:model="notes" class="form-control" rows="3"></textarea>
            @error('notes') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="form-group mt-4">
            <label class="form-label">{{ __('Add Product') }}</label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="product_search" class="form-control" placeholder="{{ __('Search products...') }}">
                @if($this->products->count() > 0)
                    <div class="absolute z-10 w-full mt-1 bg-slate-800 border border-slate-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        @foreach($this->products as $product)
                            <button type="button" wire:click="selectProduct({{ $product->id }})" class="w-full text-left px-4 py-2 hover:bg-slate-700">
                                <div class="font-medium">{{ $product->name }}</div>
                                <div class="text-sm text-gray-400">{{ $this->currency_symbol }}{{ number_format($product->price, 2) }} / {{ $product->unit }}</div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <h4 class="card-title">{{ __('Items') }}</h4>
            @foreach($items as $index => $item)
                <div class="card mb-3" style="background: rgba(255,255,255,0.03);">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-5 form-group mb-0">
                            <label class="form-label">{{ __('Description') }} *</label>
                            <input type="text" wire:model="items.{{ $index }}.description" class="form-control">
                        </div>
                        <div class="md:col-span-2 form-group mb-0">
                            <label class="form-label">{{ __('Qty') }} *</label>
                            <input type="number" step="0.01" wire:model="items.{{ $index }}.quantity" wire:change="updateItemTotal({{ $index }})" class="form-control">
                        </div>
                        <div class="md:col-span-2 form-group mb-0">
                            <label class="form-label">{{ __('Price') }} *</label>
                            <input type="number" step="0.01" wire:model="items.{{ $index }}.unit_price" wire:change="updateItemTotal({{ $index }})" class="form-control">
                        </div>
                        <div class="md:col-span-2 form-group mb-0">
                            <label class="form-label">{{ __('Tax %') }}</label>
                            <input type="number" step="0.01" wire:model="items.{{ $index }}.tax_rate" wire:change="updateItemTotal({{ $index }})" class="form-control">
                        </div>
                        <div class="md:col-span-1 flex justify-between items-center">
                            <div class="font-medium">{{ $this->currency_symbol }}{{ number_format($item['total'], 2) }}</div>
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-300">{{ __('Remove') }}</button>
                        </div>
                    </div>
                    @error('items.'.$index.'.description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('items.'.$index.'.quantity') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('items.'.$index.'.unit_price') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <button type="button" wire:click="addItem" class="btn btn-secondary">+ {{ __('Add Item') }}</button>
        </div>

        <div class="form-group mt-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="is_recurring" class="rounded border-slate-600 bg-slate-800 text-indigo-500">
                <span>{{ __('Recurring Invoice') }}</span>
            </label>
        </div>

        @if($is_recurring)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="form-group">
                    <label class="form-label">{{ __('Frequency') }}</label>
                    <select wire:model="recurring_frequency" class="form-control">
                        <option value="weekly">{{ __('Weekly') }}</option>
                        <option value="monthly">{{ __('Monthly') }}</option>
                        <option value="quarterly">{{ __('Quarterly') }}</option>
                        <option value="yearly">{{ __('Yearly') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Next Run Date') }}</label>
                    <input type="date" wire:model="next_run_date" class="form-control">
                </div>
            </div>
        @endif

        <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-700">
            <div class="text-xl font-bold">
                {{ __('Total') }}: {{ $this->currency_symbol }}{{ number_format($this->totals['grand_total'], 2) }}
            </div>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                {{ __('Create Invoice') }}
            </button>
        </div>
    </form>
</div>
@endsection
