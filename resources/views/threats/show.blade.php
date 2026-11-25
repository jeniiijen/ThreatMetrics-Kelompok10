@extends('layouts.app')

@section('content')

@php
    $record = $threatmetric ?? $threat ?? null;
    $recordId = data_get($record, 'id');
    $threatCode = data_get($record, 'threat_id')
        ?? ($recordId ? 'TH-' . str_pad((string) $recordId, 4, '0', STR_PAD_LEFT) : '-');
    $threatDate = data_get($record, 'date');
    $threatUser = data_get($record, 'user', '-');
    $threatType = strtoupper((string) data_get($record, 'type', ''));
    $threatValue = data_get($record, 'value', '-');
    $rawTags = data_get($record, 'tags');

    if (is_string($rawTags)) {
        $decoded = json_decode($rawTags, true);
        $tags = is_array($decoded) ? $decoded : explode(',', $rawTags);
    } elseif (is_array($rawTags)) {
        $tags = $rawTags;
    } else {
        $tags = [];
    }
@endphp

<div class="container-fluid px-4 py-4">

    {{-- Header and navigation actions --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">Threat Detail</h2>
            <p class="text-secondary mb-0">Detail indikator ancaman {{ $threatCode }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ \Illuminate\Support\Facades\Route::has('threatmetrics.index') ? route('threatmetrics.index') : route('dashboard') }}"
               class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            @if(!empty($recordId))
                <a href="{{ route('threatmetrics.pdf', ['threatmetric' => $recordId]) }}"
                   class="btn btn-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </a>

                <a href="{{ route('threatmetrics.edit', ['threatmetric' => $recordId]) }}"
                   class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
            @endif
        </div>
    </div>

    {{-- Threat metadata card --}}
    <div class="card"
         style="background:#1e2b37; border:1px solid #304252; border-radius:12px;">
        <div class="card-body p-4 text-white">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-secondary d-block">Threat ID</small>
                    <span class="fw-bold" style="color:#159be8;">{{ $threatCode }}</span>
                </div>

                <div class="col-md-4">
                    <small class="text-secondary d-block">Date Time</small>
                    <span>{{ $threatDate ? \Carbon\Carbon::parse($threatDate)->format('d M Y, H:i') : '-' }}</span>
                </div>

                <div class="col-md-4">
                    <small class="text-secondary d-block">User</small>
                    <span>{{ $threatUser }}</span>
                </div>

                <div class="col-md-4">
                    <small class="text-secondary d-block">Type</small>
                    <span class="badge rounded-pill px-3 py-2"
                          style="background:#19435e; color:#64baff;">
                        {{ $threatType ?: '-' }}
                    </span>
                </div>

                <div class="col-md-8">
                    <small class="text-secondary d-block">Indicator / Value</small>
                    <span class="fw-semibold" style="word-break:break-all;">{{ $threatValue }}</span>
                </div>

                {{-- Indicator tags --}}
                <div class="col-12">
                    <small class="text-secondary d-block mb-1">Tags</small>
                    <div class="d-flex flex-wrap gap-1">
                        @forelse($tags as $tag)
                            @if(trim((string) $tag) !== '')
                                <span class="badge rounded-pill px-2 py-2"
                                      style="background:#19435e; color:#64baff; font-size:11px;">
                                    {{ trim((string) $tag) }}
                                </span>
                            @endif
                        @empty
                            <span class="text-secondary">-</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    body {
        background-color: #17232f !important;
    }
</style>

@endsection