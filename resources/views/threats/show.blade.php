@extends('layouts.app')

@section('content')
<style>
    .threat-page {
        color: #e8f0f7;
    }

    .threat-page .page-subtitle,
    .threat-muted {
        color: #91a4b7;
    }

    .threat-panel {
        background: #172534;
        border: 1px solid #304457;
        border-radius: 12px;
    }

    .threat-detail-box {
        background: #1e3041;
        border: 1px solid #304457;
        border-radius: 8px;
        padding: 15px;
        height: 100%;
    }

    .threat-detail-label {
        color: #91a4b7;
        font-size: 12px;
        margin-bottom: 7px;
    }

    .threat-detail-value {
        color: #e8f0f7;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .threat-id {
        color: #159be8;
    }
</style>

<div class="container-fluid py-4 threat-page">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Threat Details</h3>
            <p class="page-subtitle mb-0">View complete threat information.</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('threatmetrics.index') }}"
               class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            {{-- Unduh laporan PDF - Anggota 5 --}}
            <a href="{{ route('threatmetrics.pdf', ['threatmetric' => $threat->id]) }}"
               class="btn btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </a>

            <a href="{{ route('threatmetrics.edit', ['threatmetric' => $threat->id]) }}"
               class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="threat-panel p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="threat-muted mb-1">THREAT IDENTIFIER</div>
                <h4 class="threat-id mb-0">{{ $threat->threat_id }}</h4>
            </div>

            <span class="badge rounded-pill"
                  style="background:#263b4e;color:#9bcff0;">
                Threat Record
            </span>
        </div>

        <hr style="border-color:#304457;">

        <div class="row g-3 mt-2">

            <div class="col-md-6">
                <div class="threat-detail-box">
                    <div class="threat-detail-label">Date</div>
                    <p class="threat-detail-value">
                        {{ $threat->date ? $threat->date->format('d M Y, H:i') : '-' }}
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="threat-detail-box">
                    <div class="threat-detail-label">User</div>
                    <p class="threat-detail-value">{{ $threat->user ?? '-' }}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="threat-detail-box">
                    <div class="threat-detail-label">Type</div>
                    <p class="threat-detail-value">{{ $threat->type ?? '-' }}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="threat-detail-box">
                    <div class="threat-detail-label">Value</div>
                    <p class="threat-detail-value">{{ $threat->value ?? '-' }}</p>
                </div>
            </div>

            <div class="col-12">
                <div class="threat-detail-box">
                    <div class="threat-detail-label">Tags</div>

                    @forelse(($threat->tags ?? []) as $tag)
                        <span class="badge me-1 mb-1"
                              style="background:#263b4e;color:#b8d8ee;">
                            {{ $tag }}
                        </span>
                    @empty
                        <span class="threat-muted">No tags available.</span>
                    @endforelse
                </div>
            </div>

            <div class="col-12">
                <div class="threat-detail-box">
                    <div class="threat-detail-label">Tweet / Description</div>
                    <p class="threat-detail-value"
                       style="white-space:pre-wrap;">{{ $threat->tweet ?: '-' }}</p>
                </div>
            </div>

            {{-- Catatan / Analisis (Quill rich text) - Anggota 5 --}}
            <div class="col-12">
                <div class="threat-detail-box">
                    <div class="threat-detail-label">
                        <i class="bi bi-journal-text me-1"></i>
                        Catatan / Analisis Threat
                    </div>
                    @if($threat->notes)
                        <div class="threat-detail-value threat-notes-content"
                             style="overflow-wrap:anywhere;">
                            {!! $threat->notes !!}
                        </div>
                    @else
                        <p class="threat-detail-value threat-muted">
                            Belum ada catatan/analisis untuk ancaman ini.
                        </p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Konten catatan Quill - pastikan teks terbaca di tema gelap */
    .threat-notes-content {
        color: #e8f0f7;
        line-height: 1.6;
    }
    .threat-notes-content h1,
    .threat-notes-content h2,
    .threat-notes-content h3 {
        color: #ffffff;
        margin: 0.6em 0 0.3em;
    }
    .threat-notes-content ul,
    .threat-notes-content ol {
        padding-left: 1.5em;
        margin: 0.4em 0;
    }
    .threat-notes-content blockquote {
        border-left: 3px solid #159be8;
        padding-left: 12px;
        color: #b8d8ee;
        margin: 0.4em 0;
    }
    .threat-notes-content a {
        color: #159be8;
    }
</style>
@endsection
