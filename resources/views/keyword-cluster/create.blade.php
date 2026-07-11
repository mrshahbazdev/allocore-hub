@extends('layouts.app')

@section('title', 'New Keyword Cluster — Allocore')
@section('page-title', '🔍 New Keyword Cluster')

@section('content')

<div class="card" style="max-width: 640px;">
    <div class="card-title">Generate a topic cluster</div>

    @if(!$geminiConfigured)
        <div class="alert alert-error">
            Gemini is not configured. Set GEMINI_API_KEY in your environment to generate clusters.
        </div>
    @endif

    <form method="POST" action="{{ route('keyword-cluster.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="topic">Topic / pillar keyword</label>
            <input type="text" id="topic" name="topic" value="{{ old('topic') }}" class="form-control" placeholder="e.g. content marketing for SaaS" required>
            <x-input-error :messages="$errors->get('topic')" />
        </div>

        <div class="form-group">
            <label class="form-label" for="website">Website or audience</label>
            <input type="text" id="website" name="website" value="{{ old('website') }}" class="form-control" placeholder="example.com" required>
            <x-input-error :messages="$errors->get('website')" />
        </div>

        <div class="form-group" style="display:flex; gap:8px;">
            <a href="{{ route('keyword-cluster.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Generate cluster</button>
        </div>
    </form>
</div>

@endsection
