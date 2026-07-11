@extends('layouts.admin')
@section('title', __('KPI Thresholds') . ' — Allocore Admin')
@section('page-title', '⚙ ' . __('Configure KPI thresholds'))

@section('content')

<div style="font-size:12px; color:#64748b; margin-bottom:20px; line-height:1.6; background:rgba(220,38,38,0.05); border:1px solid rgba(220,38,38,0.15); padding:12px 16px; border-radius:8px;">
    ⚠ {{ __('Thresholds control the traffic-light system. Changes only affect new calculations. Existing KPI results must be recalculated.') }}
</div>

@foreach($grouped as $tool => $thresholds)
<div class="card" style="margin-bottom:16px;">
    <div class="card-title" style="margin-bottom:18px;">
        @php $icons = ['gmbh'=>'📊','jahresabschluss'=>'📈','immobilien'=>'🏘']; @endphp
        {{ $icons[$tool] ?? '📋' }} {{ ucfirst($tool) }} {{ __('KPIs') }} ({{ $thresholds->count() }})
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:140px;">{{ __('KPI') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Good') }}</th>
                <th>{{ __('Warning') }}</th>
                <th>{{ __('Weight') }}</th>
                <th>{{ __('Lower is better') }}</th>
                <th>{{ __('Active') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($thresholds as $t)
        <tr>
            <td>
                <div style="font-weight:500; color:#e2e8f0; font-size:11px;">{{ $t->kpi_name }}</div>
                <div style="font-size:10px; color:#475569;">{{ $t->kpi_code }}</div>
            </td>
            <td style="color:#64748b; font-size:11px;">{{ $t->unit }}</td>
            <td>
                <form method="POST" action="{{ route('admin.thresholds.update', $t) }}" id="form-{{ $t->id }}">
                @csrf @method('PATCH')
                @if($t->lower_is_better)
                    <input type="number" step="0.01" name="green_max" class="form-control" style="width:80px;" value="{{ $t->green_max }}" title="{{ __('Green') }} {{ __('MAX') }}">
                @else
                    <input type="number" step="0.01" name="green_min" class="form-control" style="width:80px;" value="{{ $t->green_min }}" title="{{ __('Green') }} {{ __('MIN') }}">
                @endif
            </td>
            <td>
                @if($t->lower_is_better)
                    <input type="number" step="0.01" name="yellow_max" class="form-control" style="width:80px;" value="{{ $t->yellow_max }}" title="{{ __('Yellow') }} {{ __('MAX') }}">
                @else
                    <input type="number" step="0.01" name="yellow_min" class="form-control" style="width:80px;" value="{{ $t->yellow_min }}" title="{{ __('Yellow') }} {{ __('MIN') }}">
                @endif
            </td>
            <td>
                <input type="number" step="1" name="weight" class="form-control" style="width:60px;" value="{{ $t->weight ?? 0 }}">
            </td>
            <td style="text-align:center;">
                <input type="checkbox" name="lower_is_better" value="1" {{ $t->lower_is_better ? 'checked' : '' }}
                    style="width:16px; height:16px; accent-color:#dc2626;">
            </td>
            <td style="text-align:center;">
                <input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}
                    style="width:16px; height:16px; accent-color:#34d399;">
            </td>
            <td>
                <button type="submit" class="btn btn-primary btn-sm">✓</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endforeach

@endsection
