@extends('layouts.app')

@section('title', 'ThreatMetrics | Security Dashboard')

@php
    $chartTypeLabels = $typeCounts->pluck('type')->values();
    $chartTypeValues = $typeCounts->pluck('total')->map(fn ($value) => (int) $value)->values();
    $chartTrendLabels = $trendLabels;
    $chartTrendValues = $trendValues;
    $todayWibStr = $nowWib->format('Y-m-d');
    $sevenDaysAgoStr = $nowWib->copy()->subDays(6)->format('Y-m-d');
    $oneMonthAgoStr = $nowWib->copy()->subMonth()->format('Y-m-d');
@endphp

@push('styles')
<style>
    .tm-dashboard {
        --tm-bg: #0b1120;
        --tm-panel: #111a2a;
        --tm-panel2: #152235;
        --tm-border: #26364b;
        --tm-muted: #8295ad;
        --tm-cyan: #13c9dc;
        --tm-blue: #268cf0;
        color: #f2f6fc;
        background: var(--tm-bg);
        min-height: 100vh;
        margin: -24px -36px;
        padding-bottom: 36px;
    }

    .tm-dashboard * {
        box-sizing: border-box;
    }

    .tm-refresh-notice {
        padding: 12px 16px;
        border-radius: 9px;
        margin-bottom: 18px;
        font-size: 12px;
        border: 1px solid var(--tm-border);
    }

    .tm-refresh-notice.success {
        display: block;
        color: #37dfa5;
        background: #103a32;
        border-color: #176c54;
    }

    .tm-refresh-notice.error {
        display: block;
        color: #ff8995;
        background: #482631;
        border-color: #84404d;
    }

    @keyframes tm-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .tm-spin {
        animation: tm-spin 1s linear infinite;
    }

    .tm-content {
        padding: 28px;
        max-width: 1800px;
        margin: auto;
    }

    .tm-eyebrow {
        color: var(--tm-cyan);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 3px;
        text-transform: uppercase;
    }

    .tm-heading {
        color: #f5f8ff;
        font-size: clamp(23px, 3vw, 31px);
        font-weight: 800;
        margin: 8px 0 5px;
    }

    .tm-description {
        color: #91a4bd;
        font-size: 13px;
    }

    .tm-clock {
        background: var(--tm-panel);
        border: 1px solid var(--tm-border);
        border-radius: 13px;
        padding: 13px 17px;
        min-width: 190px;
    }

    .tm-clock small {
        display: block;
        color: var(--tm-muted);
        font-size: 9px;
        letter-spacing: 1.5px;
    }

    .tm-clock strong {
        color: #fff;
        font-size: 14px;
    }

    .tm-clock span {
        display: block;
        color: var(--tm-cyan);
        font-size: 11px;
    }

    .tm-filter {
        background: var(--tm-panel);
        border: 1px solid var(--tm-border);
        border-radius: 12px;
        padding: 14px 16px;
        margin-top: 24px;
        margin-bottom: 20px;
    }

    .tm-filter label {
        color: var(--tm-muted);
        font-size: 11px;
        margin-bottom: 5px;
    }

    .tm-filter .form-control {
        background: #0c1625 !important;
        color: #e8f1fc !important;
        border-color: var(--tm-border) !important;
        font-size: 12px;
        min-height: 37px;
    }

    .tm-filter .btn {
        min-height: 37px;
        font-size: 12px;
    }

    .tm-range-btn {
        background: #0c1625;
        color: #a4b5ca;
        border: 1px solid var(--tm-border);
        border-radius: 7px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: .15s;
    }

    .tm-range-btn:hover,
    .tm-range-btn.active {
        color: #0b1120;
        background: var(--tm-cyan);
        border-color: var(--tm-cyan);
    }

    .tm-stat {
        height: 100%;
        padding: 19px;
        border-radius: 13px;
        background: var(--tm-panel);
        border: 1px solid var(--tm-border);
        transition: .2s;
    }

    .tm-stat:hover {
        border-color: #17677a;
        transform: translateY(-2px);
    }

    .tm-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tm-stat-label {
        color: #a4b5ca;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .tm-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #102e40;
        color: var(--tm-cyan);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }

    .tm-stat-number {
        color: #fff;
        font-size: 30px;
        font-weight: 800;
        margin-top: 13px;
        line-height: 1.2;
    }

    .tm-stat-note {
        color: var(--tm-muted);
        font-size: 11px;
        margin-top: 7px;
    }

    .tm-panel {
        background: var(--tm-panel);
        border: 1px solid var(--tm-border);
        border-radius: 13px;
        overflow: hidden;
        height: 100%;
    }

    .tm-panel-header {
        padding: 17px 19px;
        background: var(--tm-panel2);
        border-bottom: 1px solid var(--tm-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tm-panel-title {
        color: #f4f7fc;
        font-size: 14px;
        font-weight: 700;
        margin: 0;
    }

    .tm-panel-subtitle {
        color: var(--tm-muted);
        font-size: 11px;
        margin-top: 4px;
    }

    .tm-panel-body {
        padding: 18px;
    }

    .tm-chart-area {
        height: 270px;
        position: relative;
    }

    .tm-chart-tag {
        color: var(--tm-cyan);
        background: #102e40;
        border: 1px solid #174e63;
        border-radius: 6px;
        padding: 6px 9px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .tm-empty {
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--tm-muted);
        font-size: 12px;
        padding: 20px;
    }

    .tm-alert-item {
        padding: 15px 17px;
        border-bottom: 1px solid var(--tm-border);
    }

    .tm-alert-item:last-child {
        border-bottom: none;
    }

    .tm-alert-meta {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 9px;
    }

    .tm-alert-type {
        font-size: 9px;
        font-weight: 800;
        padding: 5px 8px;
        border-radius: 5px;
        background: #18334b;
        color: #54b8ff;
    }

    .tm-alert-type.type-ip {
        background: #44391f;
        color: #f5ca52;
    }

    .tm-alert-type.type-domain {
        background: #123e36;
        color: #37dfa5;
    }

    .tm-alert-type.type-hash {
        background: #47273b;
        color: #ff7199;
    }

    .tm-alert-date {
        color: var(--tm-muted);
        font-size: 10px;
    }

    .tm-alert-value {
        color: #f5f8fc;
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
        margin-bottom: 8px;
    }

    .tm-alert-user {
        color: var(--tm-muted);
        font-size: 10px;
    }

    .tm-alert-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 9px;
    }

    .tm-alert-tag {
        color: #72c6ff;
        background: #18334b;
        border-radius: 5px;
        padding: 4px 7px;
        font-size: 9px;
    }

    .tm-ioc-section {
        margin-top: 28px;
    }

    .tm-ioc-card {
        background: var(--tm-panel);
        border: 1px solid var(--tm-border);
        border-radius: 13px;
        overflow: hidden;
    }

    .tm-ioc-table thead th {
        background: var(--tm-panel2) !important;
        color: var(--tm-muted) !important;
        border-bottom: 1px solid var(--tm-border) !important;
        font-size: 11px;
        letter-spacing: .7px;
    }

    .tm-ioc-table tbody tr {
        background: var(--tm-panel);
        border-top: 1px solid var(--tm-border);
    }

    .tm-ioc-table tbody tr:hover {
        background: #152235 !important;
    }

    .tm-delete-confirm-box {
        background: #2b1822;
        border: 1px solid #84404d;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .tm-footer {
        text-align: center;
        color: #61758e;
        font-size: 10px;
        padding: 28px 10px 6px;
    }

    @media (max-width: 768px) {
        .tm-dashboard {
            margin: -16px -14px;
        }

        .tm-content {
            padding: 20px 14px;
        }

        .tm-chart-area {
            height: 230px;
        }
    }
</style>
@endpush

@section('content')

<div class="tm-dashboard" id="dashboard">

    {{-- Main dashboard content without duplicate navbar --}}
    <div class="tm-content">

        <div id="apiRefreshNotice" class="tm-refresh-notice" role="status" aria-live="polite" style="display:none;"></div>

        @if(session('success'))
            <div class="tm-refresh-notice success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="tm-refresh-notice error">
                {{ session('error') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="tm-eyebrow">
                    <i class="bi bi-circle-fill me-1"></i>
                    Security Operations Center
                </div>
                <h1 class="tm-heading">Threat Intelligence Overview</h1>
                <p class="tm-description mb-0">
                    Monitor, analyze, and manage cyber threat indicators and IOC data in one place.
                </p>
            </div>

            <div class="tm-clock">
                <small>LOCAL TIME - WIB</small>
                <strong id="localDate">{{ $nowWib->format('d F Y') }}</strong>
                <span id="localTime">{{ $nowWib->format('H:i:s') }} WIB</span>
            </div>
        </div>

        {{-- Date filter --}}
        <form action="{{ route('dashboard') }}" method="GET" class="tm-filter" id="tmDashboardFilterForm">
            <div class="row align-items-end g-3">
                <div class="col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
                </div>

                <div class="col-md-3">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-funnel me-1"></i>
                        Apply Filter
                    </button>
                </div>

                <div class="col-md-auto">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Reset
                    </a>
                </div>

                <div class="col-md text-md-end">
                    <span class="tm-panel-subtitle">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                        -
                        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    </span>
                </div>
            </div>
        </form>

        {{-- Stat cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">Total Threats</span>
                        <div class="tm-stat-icon"><i class="bi bi-shield-exclamation"></i></div>
                    </div>
                    <div class="tm-stat-number">{{ number_format($totalThreats) }}</div>
                    <div class="tm-stat-note">Threat records in selected period</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">Unique Indicators</span>
                        <div class="tm-stat-icon"><i class="bi bi-fingerprint"></i></div>
                    </div>
                    <div class="tm-stat-number">{{ number_format($totalIndicators) }}</div>
                    <div class="tm-stat-note">Unique IOC values</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">Reporting Users</span>
                        <div class="tm-stat-icon"><i class="bi bi-people"></i></div>
                    </div>
                    <div class="tm-stat-number">{{ number_format($totalUsers) }}</div>
                    <div class="tm-stat-note">Unique users in selected period</div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">TweetFeed API</span>
                        <div class="tm-stat-icon"><i class="bi bi-broadcast"></i></div>
                    </div>
                    <div class="tm-stat-number" style="font-size:23px;">
                        @if($apiStatus === 'online')
                            <span style="color:#36e0a0;">ONLINE</span>
                        @elseif($apiStatus === 'offline')
                            <span style="color:#ff7180;">OFFLINE</span>
                        @else
                            <span style="color:#f5bd55;">UNKNOWN</span>
                        @endif
                    </div>
                    <div class="tm-stat-note">
                        {{ $lastApiUpdate ? 'Sync: '.$lastApiUpdate->format('d M Y, H:i').' WIB' : 'tweetfeed.live/api' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Analytics charts side-by-side (Threat Activity & IOC Type Distribution) --}}
        <div class="row g-3 mb-4" id="analytics">
            <div class="col-lg-6">
                <div class="tm-panel">
                    <div class="tm-panel-header">
                        <div>
                            <h5 class="tm-panel-title">
                                <i class="bi bi-activity me-2" style="color:var(--tm-cyan);"></i>
                                Threat Activity
                            </h5>
                            <div class="tm-panel-subtitle">
                                Threat records {{ $trendGrouping === 'month' ? 'by month' : 'by day' }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <button type="button"
                                    class="tm-range-btn {{ $startDate === $sevenDaysAgoStr && $endDate === $todayWibStr ? 'active' : '' }}"
                                    data-start="{{ $sevenDaysAgoStr }}"
                                    data-end="{{ $todayWibStr }}">
                                7 Hari Terakhir
                            </button>
                            <button type="button"
                                    class="tm-range-btn {{ $startDate === $oneMonthAgoStr && $endDate === $todayWibStr ? 'active' : '' }}"
                                    data-start="{{ $oneMonthAgoStr }}"
                                    data-end="{{ $todayWibStr }}">
                                1 Bulan
                            </button>
                            <span class="tm-chart-tag ms-1">SELECTED PERIOD</span>
                        </div>
                    </div>

                    <div class="tm-panel-body">
                        @if(!empty($trendLabels) && count($trendLabels) > 0)
                            <div class="tm-chart-area">
                                <canvas id="threatTrendChart"></canvas>
                            </div>
                        @else
                            <div class="tm-empty">
                                <div>
                                    <i class="bi bi-bar-chart-line fs-2 d-block mb-2"></i>
                                    No threat activity found in this period.
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="px-3 pb-3">
                        <span style="font-size:10px;color:var(--tm-muted);">
                            <i class="bi bi-circle-fill me-1" style="color:var(--tm-cyan);font-size:7px;"></i>
                            Incoming Threat Records
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="tm-panel">
                    <div class="tm-panel-header">
                        <div>
                            <h5 class="tm-panel-title">
                                <i class="bi bi-pie-chart-fill me-2" style="color:var(--tm-cyan);"></i>
                                IOC Type Distribution
                            </h5>
                            <div class="tm-panel-subtitle">Distribution of threat indicator types</div>
                        </div>
                        <span class="tm-chart-tag">IOC ANALYTICS</span>
                    </div>

                    <div class="tm-panel-body">
                        @if($typeCounts->isNotEmpty())
                            <div class="tm-chart-area">
                                <canvas id="iocChart"></canvas>
                            </div>
                        @else
                            <div class="tm-empty">No IOC data available for this period.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Live Alert Log placed after Analytics --}}
        <div class="mb-4" id="alerts">
            <div class="tm-panel">
                <div class="tm-panel-header">
                    <div>
                        <h5 class="tm-panel-title">
                            <i class="bi bi-bell-fill me-2" style="color:#ff7180;"></i>
                            Live Alert Log
                        </h5>
                        <div class="tm-panel-subtitle">Latest threat records from database</div>
                    </div>
                    <span class="tm-chart-tag">LATEST IOC</span>
                </div>

                <div style="max-height:340px;overflow-y:auto;">
                    @forelse($recentThreats as $threat)
                        @php
                            $type = strtoupper($threat->type ?? '');
                            $typeClass = match($type) {
                                'IP' => 'type-ip',
                                'DOMAIN' => 'type-domain',
                                'MD5', 'SHA256' => 'type-hash',
                                default => '',
                            };

                            $tagList = $threat->tags;
                            if (is_string($tagList)) {
                                $decoded = json_decode($tagList, true);
                                $tagList = is_array($decoded) ? $decoded : explode(',', $tagList);
                            }
                            if (!is_array($tagList)) {
                                $tagList = [];
                            }
                        @endphp

                        <div class="tm-alert-item">
                            <div class="tm-alert-meta">
                                <span class="tm-alert-type {{ $typeClass }}">{{ $type ?: 'UNKNOWN' }}</span>
                                <span class="tm-alert-date">
                                    {{ $threat->date ? $threat->date->timezone('Asia/Jakarta')->format('d M, H:i') : '-' }}
                                </span>
                            </div>

                            <div class="tm-alert-value">{{ $threat->value }}</div>

                            <div class="tm-alert-user">
                                <i class="bi bi-person me-1"></i>
                                {{ $threat->user }}
                                <span class="ms-2">
                                    {{ $threat->threat_id ?? 'TH-'.str_pad($threat->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            @if(count($tagList))
                                <div class="tm-alert-tags">
                                    @foreach(array_slice($tagList, 0, 4) as $tag)
                                        <span class="tm-alert-tag">#{{ ltrim(trim((string) $tag), '#') }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="tm-empty">No threat records available.</div>
                    @endforelse
                </div>

                <div class="p-3 text-center" style="border-top:1px solid var(--tm-border);">
                    <a href="#ioc-data" style="color:var(--tm-cyan);font-size:11px;text-decoration:none;">
                        Jump to IOC Data Table <i class="bi bi-arrow-down ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- IOC data table, CRUD, export/import, and PDF --}}
        <div class="tm-ioc-section" id="ioc-data">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <div class="tm-eyebrow">Indicator Management</div>
                    <h2 class="tm-heading" style="font-size:22px;margin:4px 0 0;">IOC Data &amp; Threat Records</h2>
                </div>
                <a href="{{ route('threatmetrics.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg me-1"></i> Add Threat
                </a>
            </div>

            @include('threats.partials.datatable-tools')

            {{-- Inline delete confirmation UI without browser alert/confirm --}}
            <div id="tmDeleteConfirmBox" class="tm-delete-confirm-box" style="display:none;">
                <div style="font-size:13px;color:#ffd6dc;">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
                    Hapus data IOC <strong id="tmDeleteTargetLabel"></strong>? Tindakan ini tidak bisa dibatalkan.
                </div>
                <form id="tmInlineDeleteForm" method="POST" class="d-flex gap-2 m-0">
                    @csrf
                    @method('DELETE')
                    <button type="button" id="tmCancelDeleteBtn" class="btn btn-sm btn-outline-light px-3">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-danger px-3">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus
                    </button>
                </form>
            </div>

            <div class="tm-ioc-card">
                <div class="tm-panel-header">
                    <div>
                        <h5 class="tm-panel-title">
                            <i class="bi bi-table me-2" style="color:var(--tm-cyan);"></i>
                            IOC Records Table
                        </h5>
                        <div class="tm-panel-subtitle">Search, sort, filter, export, import, and generate PDF reports</div>
                    </div>
                    <span class="tm-chart-tag">DATATABLES</span>
                </div>

                <div class="table-responsive">
                    <table id="threats-table" class="table table-borderless align-middle mb-0 tm-ioc-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-3 py-3">Date Time</th>
                                <th class="px-3 py-3">User</th>
                                <th class="px-3 py-3">Type</th>
                                <th class="px-3 py-3">Indicator / Value</th>
                                <th class="px-3 py-3">Tags</th>
                                <th class="px-3 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentThreats as $threat)
                                @php
                                    $displayId = $threat->threat_id ?? 'TH-'.str_pad($threat->id, 4, '0', STR_PAD_LEFT);
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="fw-bold" style="color:var(--tm-cyan);">
                                            {{ $displayId }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3" style="color:var(--tm-muted);font-size:13px;">
                                        {{ $threat->date ? \Carbon\Carbon::parse($threat->date)->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="px-3 py-3 fw-semibold">{{ $threat->user }}</td>
                                    <td class="px-3 py-3">
                                        <span class="badge rounded-pill px-3 py-2" style="background:#102e40;color:var(--tm-cyan);">
                                            {{ strtoupper($threat->type ?? '-') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 fw-semibold" style="font-size:13px;word-break:break-all;">
                                        {{ $threat->value }}
                                    </td>
                                    <td class="px-3 py-3">
                                        @php
                                            $tags = $threat->tags;
                                            if (is_string($tags)) {
                                                $decoded = json_decode($tags, true);
                                                $tags = is_array($decoded) ? $decoded : explode(',', $tags);
                                            }
                                            if (!is_array($tags)) {
                                                $tags = [];
                                            }
                                        @endphp
                                        @forelse($tags as $tag)
                                            <span class="badge rounded-pill px-2 py-1 me-1" style="background:#18334b;color:#72c6ff;font-size:11px;">
                                                {{ trim(str_replace('"', '', (string) $tag)) }}
                                            </span>
                                        @empty
                                            <span style="color:var(--tm-muted);">-</span>
                                        @endforelse
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('threatmetrics.show', ['threatmetric' => $threat->id]) }}"
                                               class="btn btn-sm" style="background:#102e40;color:var(--tm-cyan);border-radius:7px;" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('threatmetrics.edit', ['threatmetric' => $threat->id]) }}"
                                               class="btn btn-sm" style="background:#44391f;color:#f5ca52;border-radius:7px;" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm tm-trigger-delete"
                                                    style="background:#482631;color:#ff7180;border-radius:7px;"
                                                    data-url="{{ route('threatmetrics.destroy', ['threatmetric' => $threat->id]) }}"
                                                    data-label="{{ $displayId }} ({{ $threat->value }})"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5" style="color:var(--tm-muted);">
                                        No threat records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Anonymous Quill notes section placed below IOC table and above footer --}}
        @include('threats.partials.analyst-notes')

        <div class="tm-footer">
            THREATMETRICS CYBER THREAT INTELLIGENCE
            <br>
            Security Operations Center Dashboard &middot; Kelompok 10 &mdash; Mata Kuliah Pemrograman Web
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cyan = '#13c9dc';
    const muted = '#8295ad';
    const gridColor = 'rgba(130,149,173,0.13)';

    Chart.defaults.color = muted;
    Chart.defaults.font.family = 'Arial, sans-serif';

    const trendLabels = @json($chartTrendLabels);
    const trendValues = @json($chartTrendValues);
    const iocLabels = @json($chartTypeLabels);
    const iocValues = @json($chartTypeValues);

    // Quick range buttons (7 Hari Terakhir & 1 Bulan)
    const filterForm = document.getElementById('tmDashboardFilterForm');
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');

    document.querySelectorAll('.tm-range-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!filterForm || !startInput || !endInput) return;
            startInput.value = btn.getAttribute('data-start') || '';
            endInput.value = btn.getAttribute('data-end') || '';
            filterForm.submit();
        });
    });

    // Chart activity
    const trendCanvas = document.getElementById('threatTrendChart');
    if (trendCanvas) {
        const numericValues = trendValues.map((v) => Number(v) || 0);
        const maxVal = Math.max(0, ...numericValues);
        const suggestedMax = maxVal > 0 ? Math.ceil(maxVal * 1.15) : 10;

        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Threat Records',
                    data: numericValues,
                    borderColor: cyan,
                    backgroundColor: 'rgba(19,201,220,0.14)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: cyan,
                    pointBorderColor: '#111a2a',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => 'Threats: ' + Number(context.parsed.y || 0).toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: muted, maxRotation: 35, autoSkip: true, maxTicksLimit: 12 }
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: suggestedMax,
                        ticks: {
                            precision: 0,
                            color: muted,
                            callback: (val) => Number(val).toLocaleString('id-ID')
                        },
                        grid: { color: gridColor }
                    }
                }
            }
        });
    }

    // Chart IOC distribution (color mapped per IOC type)
    const iocCanvas = document.getElementById('iocChart');
    if (iocCanvas) {
        const typeColorMap = {
            'URL': '#54b8ff',
            'IP': '#f5ca52',
            'DOMAIN': '#37dfa5',
            'MD5': '#ff7199',
            'SHA256': '#f472b6',
            'HASH': '#ff7199'
        };
        const fallbackColors = ['#13c9dc', '#268cf0', '#37dfa5', '#f5bd55', '#a78bfa', '#ff7180'];

        const iocColors = iocLabels.map((label, idx) => {
            const key = String(label || '').toUpperCase().trim();
            return typeColorMap[key] || fallbackColors[idx % fallbackColors.length];
        });

        new Chart(iocCanvas, {
            type: 'doughnut',
            data: {
                labels: iocLabels,
                datasets: [{
                    data: iocValues,
                    backgroundColor: iocColors,
                    borderColor: '#111a2a',
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: muted, padding: 18, usePointStyle: true, pointStyle: 'circle' }
                    }
                }
            }
        });
    }

    // Clock WIB
    function updateWibClock() {
        const now = new Date();
        const dateElement = document.getElementById('localDate');
        const timeElement = document.getElementById('localTime');

        if (dateElement) {
            dateElement.textContent = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            }).format(now);
        }

        if (timeElement) {
            timeElement.textContent = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            }).format(now) + ' WIB';
        }
    }
    updateWibClock();
    setInterval(updateWibClock, 1000);

    // Inline delete confirmation without browser confirm()
    const deleteBox = document.getElementById('tmDeleteConfirmBox');
    const deleteLabel = document.getElementById('tmDeleteTargetLabel');
    const deleteForm = document.getElementById('tmInlineDeleteForm');
    const cancelDeleteBtn = document.getElementById('tmCancelDeleteBtn');

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.tm-trigger-delete');
        if (!btn || !deleteBox || !deleteForm) return;

        deleteForm.action = btn.getAttribute('data-url') || '';
        if (deleteLabel) {
            deleteLabel.textContent = btn.getAttribute('data-label') || 'terpilih';
        }
        deleteBox.style.display = 'flex';
        deleteBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    if (cancelDeleteBtn && deleteBox) {
        cancelDeleteBtn.addEventListener('click', function () {
            deleteBox.style.display = 'none';
        });
    }

    // Route DataTables error to inline UI notice instead of browser alert
    const refreshNotice = document.getElementById('apiRefreshNotice');
    if (window.jQuery && $.fn && $.fn.dataTable) {
        $.fn.dataTable.ext.errMode = 'none';
        $('#threats-table').on('error.dt', function (e, settings, techNote, message) {
            if (refreshNotice) {
                refreshNotice.className = 'tm-refresh-notice error';
                refreshNotice.style.display = 'block';
                refreshNotice.textContent = 'Gagal memuat data tabel IOC (' + (message || 'Ajax error') + ').';
            }
        });
    }

    // API sync
    const refreshForm = document.getElementById('refreshApiForm');
    const refreshButton = document.getElementById('refreshApiButton');
    const refreshIcon = document.getElementById('refreshApiIcon');
    const refreshText = document.getElementById('refreshApiText');

    if (refreshForm) {
        refreshForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            refreshButton.disabled = true;
            refreshIcon.classList.add('tm-spin');
            refreshText.textContent = 'Syncing...';
            if (refreshNotice) {
                refreshNotice.style.display = 'none';
            }

            try {
                const response = await fetch(refreshForm.action, {
                    method: 'POST',
                    body: new FormData(refreshForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'API synchronization failed.');
                }

                if (refreshNotice) {
                    refreshNotice.className = 'tm-refresh-notice success';
                    refreshNotice.style.display = 'block';
                    refreshNotice.textContent = result.message;
                }
                refreshText.textContent = 'Synced!';

                setTimeout(() => window.location.reload(), 800);
            } catch (error) {
                if (refreshNotice) {
                    refreshNotice.className = 'tm-refresh-notice error';
                    refreshNotice.style.display = 'block';
                    refreshNotice.textContent = error.message;
                }
                refreshButton.disabled = false;
                refreshIcon.classList.remove('tm-spin');
                refreshText.textContent = 'Sync API';
            }
        });
    }
});
</script>
@endpush