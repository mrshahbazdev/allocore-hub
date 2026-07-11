@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title', $project->topic.' — Keyword Cluster')
@section('page-title', '🔍 '.$project->topic)

@section('topbar-actions')
    @if($project->pillar_content)
        <a href="{{ route('keyword-cluster.export.pillar', $project) }}" class="btn btn-secondary btn-sm" download>Export Pillar</a>
    @endif
    <form method="POST" action="{{ route('keyword-cluster.destroy', $project) }}" onsubmit="return confirm('Delete this cluster?')" style="display:inline;">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
@endsection

@push('styles')
<style>
    .kc-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .kc-tab { padding: 8px 16px; border-radius: 8px; border: 1px solid rgba(99,102,241,0.2); background: rgba(255,255,255,0.05); color: #94a3b8; cursor: pointer; font-size: 13px; }
    .kc-tab.active { background: rgba(99,102,241,0.2); color: #c7d2fe; }
    .kc-tab-content { display: none; }
    .kc-tab-content.active { display: block; }
    .kc-progress-wrap { background: rgba(255,255,255,0.06); border-radius: 6px; height: 12px; overflow: hidden; margin-bottom: 12px; }
    .kc-progress-bar { background: #6366f1; height: 100%; transition: width .3s; }
    .kc-meta { font-size: 12px; color: #94a3b8; margin-bottom: 16px; }
    .kc-markdown { line-height: 1.7; color: #cbd5e1; }
    .kc-markdown h1 { font-size: 22px; font-weight: 700; color: #c7d2fe; margin: 0 0 16px; }
    .kc-markdown h2 { font-size: 18px; font-weight: 600; color: #c7d2fe; margin: 24px 0 12px; }
    .kc-markdown h3 { font-size: 15px; font-weight: 600; color: #c7d2fe; margin: 18px 0 8px; }
    .kc-markdown p { margin-bottom: 12px; }
    .kc-markdown ul { padding-left: 20px; margin-bottom: 12px; }
    .kc-markdown li { margin-bottom: 4px; }
    .kc-markdown pre { background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; overflow-x: auto; }
    .kc-markdown code { font-family: monospace; font-size: 12px; }
    .kc-raw { background: rgba(0,0,0,0.2); padding: 16px; border-radius: 8px; font-family: monospace; font-size: 12px; white-space: pre-wrap; color: #94a3b8; max-height: 600px; overflow-y: auto; }
    .kc-question { border-bottom: 1px solid rgba(255,255,255,0.04); padding: 12px 0; }
    .kc-question:last-child { border-bottom: none; }
    .kc-question strong { color: #c7d2fe; display: block; margin-bottom: 4px; }
</style>
@endpush

@section('content')

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:16px;">
        <div>
            <div style="font-size:18px; font-weight:600; color:#c7d2fe;">{{ $project->topic }}</div>
            <div class="kc-meta">{{ $project->website }} · <span class="badge badge-gray">{{ $project->statusLabel() }}</span></div>
        </div>
        @if($project->status === \App\Models\KeywordProject::STATUS_FAILED)
            <form method="POST" action="{{ route('keyword-cluster.retry', $project) }}">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">Retry</button>
            </form>
        @endif
    </div>

    @if($project->isInProgress())
        <div id="kc-progress" data-status-url="{{ route('keyword-cluster.status', $project) }}" data-reload-on-complete="1">
            <div class="kc-progress-wrap">
                <div class="kc-progress-bar" id="kc-progress-bar" style="width:{{ $project->progressPercent() }}%;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span id="kc-status-label">{{ $project->statusLabel() }}</span>
                <span id="kc-progress-text">{{ $project->progressPercent() }}%</span>
            </div>
            <div id="kc-error" style="margin-top:12px; color:#f87171;"></div>
        </div>
    @elseif($project->status === \App\Models\KeywordProject::STATUS_FAILED)
        <div class="alert alert-error">❌ {{ $project->error }}</div>
    @endif

    @if($project->status === \App\Models\KeywordProject::STATUS_COMPLETED || $project->pillar_content)
        <div class="kc-tabs" style="margin-top:24px;">
            <button class="kc-tab active" data-kc-tab="pillar">Pillar page</button>
            @foreach($project->subtopics as $subtopic)
                <button class="kc-tab" data-kc-tab="cluster-{{ $subtopic->id }}">{{ $subtopic->title }}</button>
            @endforeach
        </div>

        <div class="kc-tab-content active" id="tab-pillar">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="font-size:14px; font-weight:600; color:#c7d2fe;">Pillar Page</div>
                <div style="display:flex; gap:6px;">
                    <button type="button" class="btn btn-sm btn-secondary kc-toggle" data-target="pillar-preview">Preview</button>
                    <button type="button" class="btn btn-sm btn-secondary kc-toggle" data-target="pillar-raw">Markdown</button>
                    <a href="{{ route('keyword-cluster.export.pillar', $project) }}" class="btn btn-sm btn-secondary" download>Export .md</a>
                </div>
            </div>
            <div id="pillar-preview" class="kc-tab-panel">
                <div class="kc-markdown">
                    {!! Str::markdown($project->pillar_content ?? '') !!}
                </div>
            </div>
            <div id="pillar-raw" class="kc-tab-panel" style="display:none;">
                <pre class="kc-raw">{{ $project->pillar_content }}</pre>
            </div>
            @if($project->pillar_meta_description)
                <div class="kc-meta" style="margin-top:12px;">Meta: {{ $project->pillar_meta_description }}</div>
            @endif
        </div>

        @foreach($project->subtopics as $subtopic)
        <div class="kc-tab-content" id="tab-cluster-{{ $subtopic->id }}">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                <div>
                    <div style="font-size:14px; font-weight:600; color:#c7d2fe;">{{ $subtopic->title }}</div>
                    <div class="kc-meta">{{ $subtopic->long_tail_keyword }}</div>
                </div>
                <div style="display:flex; gap:6px;">
                    <button type="button" class="btn btn-sm btn-secondary kc-toggle" data-target="cluster-preview-{{ $subtopic->id }}">Preview</button>
                    <button type="button" class="btn btn-sm btn-secondary kc-toggle" data-target="cluster-raw-{{ $subtopic->id }}">Markdown</button>
                    <a href="{{ route('keyword-cluster.export.cluster', ['project' => $project, 'subtopic' => $subtopic->id]) }}" class="btn btn-sm btn-secondary" download>Export .md</a>
                </div>
            </div>
            <div id="cluster-preview-{{ $subtopic->id }}" class="kc-tab-panel">
                <div class="kc-markdown">
                    {!! Str::markdown($subtopic->cluster_content ?? '') !!}
                </div>
            </div>
            <div id="cluster-raw-{{ $subtopic->id }}" class="kc-tab-panel" style="display:none;">
                <pre class="kc-raw">{{ $subtopic->cluster_content }}</pre>
            </div>
            @if($subtopic->cluster_meta_description)
                <div class="kc-meta" style="margin-top:12px;">Meta: {{ $subtopic->cluster_meta_description }}</div>
            @endif

            @if($subtopic->questions->isNotEmpty())
                <div style="margin-top:24px;">
                    <div class="card-title">Questions & Answers</div>
                    @foreach($subtopic->questions as $question)
                        <div class="kc-question">
                            <strong>{{ $question->question }}</strong>
                            <p style="margin:0; color:#94a3b8;">{{ $question->answer ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endforeach
    @endif
</div>

@endsection

@push('scripts')
<script>
(() => {
    // Tabs
    document.querySelectorAll('.kc-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.kcTab;
            document.querySelectorAll('.kc-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.kc-tab-content').forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('tab-' + target).classList.add('active');
        });
    });

    // Preview / Markdown toggle
    document.querySelectorAll('.kc-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.target;
            document.querySelectorAll('.kc-tab-panel').forEach(p => p.style.display = 'none');
            document.getElementById(target).style.display = 'block';
        });
    });

    // Progress polling
    const progressEl = document.getElementById('kc-progress');
    if (progressEl) {
        const url = progressEl.dataset.statusUrl;
        const bar = document.getElementById('kc-progress-bar');
        const label = document.getElementById('kc-status-label');
        const text = document.getElementById('kc-progress-text');
        const error = document.getElementById('kc-error');

        const interval = setInterval(() => {
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    bar.style.width = data.progress_percent + '%';
                    label.textContent = data.status_label;
                    text.textContent = data.progress_percent + '%';
                    if (data.error) {
                        error.textContent = data.error;
                    }
                    if (! data.is_in_progress) {
                        clearInterval(interval);
                        if (data.status === 'completed') {
                            window.location.reload();
                        }
                    }
                })
                .catch(() => {
                    // silently retry
                });
        }, 3000);
    }
})();
</script>
@endpush
