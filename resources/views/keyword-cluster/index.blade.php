@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title', 'Keyword Cluster — Allocore')
@section('page-title', '🔍 Keyword Cluster')

@section('topbar-actions')
    <a href="{{ route('keyword-cluster.create') }}" class="btn btn-primary btn-sm">+ New Cluster</a>
@endsection

@push('styles')
<style>
    .kc-progress-wrap { width: 120px; height: 6px; background: rgba(255,255,255,0.06); border-radius: 3px; overflow: hidden; }
    .kc-progress-bar { height: 100%; background: #6366f1; transition: width .3s ease; }
    .kc-actions { display: flex; gap: 6px; justify-content: flex-end; }
    .kc-empty { text-align: center; padding: 60px 20px; }
</style>
@endpush

@section('content')

@if($projects->isEmpty())
<div class="card kc-empty">
    <div style="font-size:48px; margin-bottom:16px;">🔍</div>
    <div style="font-size:18px; font-weight:600; color:#c7d2fe; margin-bottom:8px;">No keyword clusters yet</div>
    <div style="font-size:13px; color:#475569; margin-bottom:24px;">Create your first AI-powered keyword cluster and get a pillar page plus 5 cluster pages.</div>
    <a href="{{ route('keyword-cluster.create') }}" class="btn btn-primary">🔍 Create your first cluster</a>
</div>
@else
<div class="card">
    <div style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Topic</th>
                <th>Website</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Created</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>
                    <a href="{{ route('keyword-cluster.show', $project) }}" style="font-weight:500; color:#c7d2fe; text-decoration:none;">
                        {{ $project->topic }}
                    </a>
                </td>
                <td style="color:#94a3b8;">{{ $project->website }}</td>
                <td><span class="badge badge-gray">{{ $project->statusLabel() }}</span></td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="font-size:12px; color:#94a3b8; width:30px;">{{ $project->progressPercent() }}%</div>
                        <div class="kc-progress-wrap">
                            <div class="kc-progress-bar" style="width:{{ $project->progressPercent() }}%;"></div>
                        </div>
                    </div>
                </td>
                <td style="font-size:12px; color:#475569;">{{ $project->created_at->format('d.m.Y') }}</td>
                <td style="text-align:right;">
                    <div class="kc-actions">
                        <a href="{{ route('keyword-cluster.show', $project) }}" class="btn btn-secondary btn-sm">View</a>
                        @if($project->status === \App\Models\KeywordProject::STATUS_FAILED)
                        <form method="POST" action="{{ route('keyword-cluster.retry', $project) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Retry</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('keyword-cluster.destroy', $project) }}" onsubmit="return confirm('Delete this cluster?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div class="pagination">{{ $projects->links() }}</div>
</div>
@endif

@endsection
