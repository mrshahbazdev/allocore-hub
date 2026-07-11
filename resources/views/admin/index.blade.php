@extends('layouts.admin')
@section('title', __('Admin Dashboard') . ' — Allocore')
@section('page-title', '📊 ' . __('Admin Dashboard'))

@section('content')

{{-- Stats Grid --}}
<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:14px; margin-bottom:22px;">
    @foreach([
        ['👥 ' . __('Users'), $stats['users'], '#f87171'],
        ['📋 ' . __('Analyses') . ' ' . __('Total'), $stats['analyses'], '#818cf8'],
        ['🏢 ' . __('Companies'), $stats['companies'], '#34d399'],
        ['✅ ' . __('Complete'), $stats['complete'], '#fbbf24'],
    ] as [$label, $val, $clr])
    <div class="card" style="border-color:{{ $clr }}30;">
        <div style="font-size:10px; color:#64748b; margin-bottom:6px;">{{ $label }}</div>
        <div style="font-size:34px; font-weight:700; color:{{ $clr }};">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Tool breakdown --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:22px;">
    @foreach([['📊 ' . __('GmbH Analyses'), $stats['gmbh'],'#818cf8'],['📈 ' . __('Financial Statements'), $stats['jahrabs'],'#fbbf24'],['🏘 ' . __('Real Estate'), $stats['immobilien'],'#c084fc']] as [$t,$v,$c])
    <div class="card" style="text-align:center; padding:14px; border-color:{{ $c }}25;">
        <div style="font-size:10px; color:#64748b; margin-bottom:4px;">{{ $t }}</div>
        <div style="font-size:28px; font-weight:700; color:{{ $c }};">{{ $v }}</div>
        <div style="font-size:10px; color:#475569; margin-top:2px;">{{ __('Analyses') }}</div>
    </div>
    @endforeach
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

    {{-- Recent Users --}}
    <div class="card">
        <div class="card-title">👥 {{ __('Recent Users') }}</div>
        <table class="data-table">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Role') }}</th><th>{{ __('Since') }}</th></tr></thead>
            <tbody>
            @foreach($recentUsers as $u)
            <tr>
                <td style="font-weight:500; color:#e2e8f0;">{{ $u->name }}</td>
                <td style="color:#64748b; font-size:11px;">{{ $u->email }}</td>
                <td>
                    @php $role = $u->getRoleNames()->first() ?? 'none'; @endphp
                    <span class="badge badge-{{ strtolower($role) }}">{{ $role }}</span>
                </td>
                <td style="font-size:11px; color:#475569;">{{ $u->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div style="margin-top:12px;">
            <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">{{ __('All Users') }} →</a>
        </div>
    </div>

    {{-- Top Analyses by Score --}}
    <div class="card">
        <div class="card-title">🏆 {{ __('Top Analyses') }} ({{ __('by Score') }})</div>
        <table class="data-table">
            <thead><tr><th>{{ __('Analysis') }}</th><th>{{ __('Type') }}</th><th>{{ __('User') }}</th><th>{{ __('Score') }}</th></tr></thead>
            <tbody>
            @foreach($topAnalyses as $a)
            <tr>
                <td style="font-weight:500; color:#e2e8f0; font-size:11px;">{{ Str::limit($a->name,25) }}</td>
                <td style="font-size:11px;">
                    @php $tc=['gmbh'=>'#818cf8','jahresabschluss'=>'#fbbf24','immobilien'=>'#c084fc']; @endphp
                    <span style="color:{{ $tc[$a->type]??'#94a3b8' }};">{{ $a->typeLabel() }}</span>
                </td>
                <td style="font-size:11px; color:#64748b;">{{ $a->user->name ?? '—' }}</td>
                <td>
                    @php $sc=$a->scoreColor(); $h=['green'=>'#34d399','yellow'=>'#fbbf24','red'=>'#f87171','gray'=>'#64748b'][$sc]; @endphp
                    <span style="font-weight:700; color:{{ $h }};">{{ number_format($a->total_score,1) }}</span>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div style="margin-top:12px;">
            <a href="{{ route('analyses.index') }}" class="btn btn-secondary btn-sm">{{ __('All Analyses') }} →</a>
        </div>
    </div>

</div>

@endsection
