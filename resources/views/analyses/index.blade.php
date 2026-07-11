@extends('layouts.app')
@section('title', __('All Analyses') . ' — Allocore')
@section('page-title', '📋 ' . __('All Analyses'))
@push('styles')
<style>
    .analyses-card {
        overflow: hidden;
    }
    .analyses-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .analyses-pagination {
        margin-top: 12px;
    }
</style>
@endpush
@section('content')
<div class="card analyses-card">
    <div class="analyses-table-wrap">
    <table class="data-table">
        <thead>
            <tr><th>{{ __('Analysis') }}</th><th>{{ __('Company') }}</th><th>{{ __('Type') }}</th><th>{{ __('Score') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($analyses as $a)
            <tr>
                <td style="font-weight:500; color:#c7d2fe;">{{ $a->name }}</td>
                <td style="color:#94a3b8;">{{ $a->company->name ?? '—' }}</td>
                <td>
                    @php $tc = ['gmbh'=>'#818cf8','jahresabschluss'=>'#fbbf24','immobilien'=>'#c084fc']; @endphp
                    <span style="font-size:11px; color:{{ $tc[$a->type] ?? '#94a3b8' }}; font-weight:500;">{{ $a->typeLabel() }}</span>
                </td>
                <td>
                    @if($a->total_score !== null)
                        <span class="score-{{ $a->scoreColor() }}" style="font-weight:700;">{{ number_format($a->total_score,1) }}</span>
                        <span style="font-size:11px; color:#475569;">/100</span>
                    @else <span style="color:#475569;">—</span> @endif
                </td>
                <td><span class="badge badge-{{ $a->status === 'complete' ? 'green' : 'gray' }}">{{ $a->status }}</span></td>
                <td style="font-size:12px; color:#475569;">{{ $a->created_at->format('d.m.Y') }}</td>
                <td>
                    <a href="{{ route($a->type . '.show', $a) }}" class="btn btn-secondary btn-sm">→ {{ __('Open') }}</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center; color:#475569; padding:40px;">{{ __('No analyses available.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="pagination analyses-pagination">{{ $analyses->links() }}</div>
</div>
@endsection
