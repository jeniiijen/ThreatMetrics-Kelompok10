@extends('layouts.app')

@section('title', 'ThreatMetrics | Security Dashboard')

@php
    $chartTypeLabels = $typeCounts->pluck('type')->values();
    $chartTypeValues = $typeCounts->pluck('total')
        ->map(fn ($value) => (int) $value)->values();

    $chartTrendLabels = $trendLabels;
    $chartTrendValues = $trendValues;
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
        margin: -24px -40px;
        padding-bottom: 35px;
    }

    .tm-dashboard * {
        box-sizing: border-box;
    }

    /* NAVBAR */
    .tm-navbar {
        min-height: 76px;
        padding: 12px 28px;
        background: #101827;
        border-bottom: 1px solid var(--tm-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .tm-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: white;
    }

    .tm-brand-icon {
        width: 43px;
        height: 43px;
        border: 1px solid #14566a;
        background: #102b3c;
        color: var(--tm-cyan);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 22px;
    }

    .tm-brand-name {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 1.5px;
    }

    .tm-brand-name span {
        color: var(--tm-cyan);
    }

    .tm-brand-subtitle {
        font-size: 9px;
        color: var(--tm-muted);
        letter-spacing: 2px;
    }

    .tm-nav-links {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .tm-nav-links a {
        text-decoration: none;
        color: #a5b4c8;
        padding: 11px 16px;
        border-radius: 8px;
        font-size: 13px;
        transition: .2s;
    }

    .tm-nav-links a:hover,
    .tm-nav-links a.active {
        color: var(--tm-cyan);
        background: #102b3c;
        border: 1px solid #17475a;
    }

    
    .tm-refresh:disabled {
        opacity: .6;
        cursor: not-allowed;
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



    .tm-refresh:hover {
        background: #164056;
        color: white;
    }

    /* MAIN */
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
        color: white;
        font-size: 14px;
    }

    .tm-clock span {
        display: block;
        color: var(--tm-cyan);
        font-size: 11px;
    }

    /* FILTER */
    .tm-filter {
        background: var(--tm-panel);
        border: 1px solid var(--tm-border);
        border-radius: 12px;
        padding: 14px 16px;
        margin-top: 25px;
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

    /* STAT CARDS */
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
        color: white;
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

    /* PANELS */
    .tm-panel {
        background: var(--tm-panel);
        border: 1px solid var(--tm-border);
        border-radius: 13px;
        overflow: hidden;
        height: 100%;
    }

    .tm-panel-header {
        padding: 17px 19px;
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
        margin-top: 5px;
    }

    .tm-panel-body {
        padding: 18px;
    }

    .tm-chart-area {
        height: 280px;
        position: relative;
    }

    .tm-chart-area.small {
        height: 245px;
    }

    .tm-chart-tag {
        color: var(--tm-cyan);
        background: #102e40;
        border-radius: 6px;
        padding: 6px 9px;
        font-size: 9px;
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

    /* API STATUS */
    .tm-api-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 800;
    }

    .tm-api-status.online {
        color: #36e0a0;
        background: #103a32;
    }

    .tm-api-status.offline {
        color: #ff6e7e;
        background: #482631;
    }

    .tm-api-status.unknown {
        color: #f5bd55;
        background: #44371e;
    }

    .tm-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        background: currentColor;
    }

    /* ALERT LOG */
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

    .tm-footer {
        text-align: center;
        color: #61758e;
        font-size: 10px;
        padding: 25px 10px 5px;
    }

    @media (max-width: 768px) {
        .tm-dashboard {
            margin: -20px -14px;
        }

        .tm-navbar {
            padding: 13px 16px;
        }

        .tm-content {
            padding: 20px 15px;
        }

        .tm-nav-links {
            width: 100%;
            order: 3;
            overflow-x: auto;
        }

        .tm-nav-links a {
            padding: 9px 11px;
            white-space: nowrap;
        }

        .tm-chart-area {
            height: 230px;
        }
    }
</style>
@endpush

@section('content')

<div class="tm-dashboard">

    {{-- NAVBAR --}}
    <nav class="tm-navbar">

        <a href="{{ route('dashboard') }}" class="tm-brand">
            <div class="tm-brand-icon">
                <i class="bi bi-shield-check"></i>
            </div>

            <div>
                <div class="tm-brand-name">
                    THREAT<span>METRICS</span>
                </div>
                <div class="tm-brand-subtitle">
                    CYBER THREAT INTELLIGENCE
                </div>
            </div>
        </a>

        <div class="tm-nav-links">
            <a href="{{ route('dashboard') }}" class="active">
                <i class="bi bi-grid me-1"></i> Dashboard
            </a>

            <a href="{{ route('threatmetrics.index') }}">
                <i class="bi bi-database me-1"></i> IOC Data
            </a>

            <a href="#analytics">
                <i class="bi bi-bar-chart me-1"></i> Analytics
            </a>

            <a href="#alerts">
                <i class="bi bi-bell me-1"></i> Alerts
            </a>
        </div>

        
        <form id="refreshApiForm"
            action="{{ route('tweetfeed.sync') }}"
            method="POST">

            @csrf

            <input type="hidden" name="range" value="today">

            <button type="submit"
                    id="refreshApiButton"
                    class="tm-refresh">

                <i class="bi bi-arrow-repeat me-1"
                id="refreshApiIcon"></i>

                <span id="refreshApiText">Refresh API</span>

            </button>
        </form>


    </nav>


    {{-- MAIN CONTENT --}}
    <div class="tm-content">

        <div id="apiRefreshNotice"
            class="tm-refresh-notice"
            role="status"
            aria-live="polite"
            style="display:none;">
        </div>

        {{-- PAGE HEADER --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>
                <div class="tm-eyebrow">
                    <i class="bi bi-circle-fill me-1"></i>
                    Security Operations Center
                </div>

                <h1 class="tm-heading">
                    Threat Intelligence Overview
                </h1>

                <p class="tm-description mb-0">
                    Monitor, analyze, and manage cyber threat indicators.
                </p>
            </div>

            
            <div class="tm-clock">
                <small>LOCAL TIME - WIB</small>

                <strong id="localDate">
                    {{ $nowWib->format('d F Y') }}
                </strong>

                <span id="localTime">
                    {{ $nowWib->format('H:i:s') }} WIB
                </span>
            </div>



        </div>


        {{-- DATE FILTER --}}
        <form action="{{ route('dashboard') }}"
              method="GET"
              class="tm-filter">

            <div class="row align-items-end g-3">

                <div class="col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           class="form-control"
                           value="{{ $startDate }}"
                           required>
                </div>

                <div class="col-md-3">
                    <label for="end_date">End Date</label>
                    <input type="date"
                           name="end_date"
                           id="end_date"
                           class="form-control"
                           value="{{ $endDate }}"
                           required>
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-funnel me-1"></i>
                        Apply Filter
                    </button>
                </div>

                <div class="col-md-auto">
                    <a href="{{ route('dashboard') }}"
                       class="btn btn-outline-secondary">
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


        {{-- STATISTIC CARDS --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">Total Threats</span>
                        <div class="tm-stat-icon">
                            <i class="bi bi-shield-exclamation"></i>
                        </div>
                    </div>

                    <div class="tm-stat-number">
                        {{ number_format($totalThreats) }}
                    </div>

                    <div class="tm-stat-note">
                        Threat records in selected period
                    </div>
                </div>
            </div>


            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">Unique Indicators</span>
                        <div class="tm-stat-icon">
                            <i class="bi bi-fingerprint"></i>
                        </div>
                    </div>

                    <div class="tm-stat-number">
                        {{ number_format($totalIndicators) }}
                    </div>

                    <div class="tm-stat-note">
                        Unique IOC values
                    </div>
                </div>
            </div>


            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">Reporting Users</span>
                        <div class="tm-stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>

                    <div class="tm-stat-number">
                        {{ number_format($totalUsers) }}
                    </div>

                    <div class="tm-stat-note">
                        Unique users in selected period
                    </div>
                </div>
            </div>


            <div class="col-6 col-xl-3">
                <div class="tm-stat">
                    <div class="tm-stat-top">
                        <span class="tm-stat-label">TweetFeed API</span>
                        <div class="tm-stat-icon">
                            <i class="bi bi-broadcast"></i>
                        </div>
                    </div>

                    <div class="tm-stat-number"
                         style="font-size:23px;">

                        @if($apiStatus === 'online')
                            <span style="color:#36e0a0;">ONLINE</span>
                        @elseif($apiStatus === 'offline')
                            <span style="color:#ff7180;">OFFLINE</span>
                        @else
                            <span style="color:#f5bd55;">UNKNOWN</span>
                        @endif

                    </div>

                    <div class="tm-stat-note">
                        Current API connection status
                    </div>
                </div>
            </div>

        </div>


        {{-- MAIN ANALYTICS --}}
        <div class="row g-3 mb-4">

            {{-- THREAT ACTIVITY CHART --}}
            <div class="col-lg-8">

                <div class="tm-panel">

                    <div class="tm-panel-header">

                        <div>
                            <h5 class="tm-panel-title">
                                <i class="bi bi-activity me-2"
                                   style="color:var(--tm-cyan);"></i>
                                Threat Activity
                            </h5>

                            <div class="tm-panel-subtitle">
                                Threat records
                                {{ $trendGrouping === 'month' ? 'by month' : 'by day' }}
                            </div>
                        </div>

                        <span class="tm-chart-tag">
                            SELECTED PERIOD
                        </span>

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
                            <i class="bi bi-circle-fill me-1"
                               style="color:var(--tm-cyan);font-size:7px;"></i>
                            Incoming Threat Records
                        </span>
                    </div>

                </div>

            </div>


            {{-- LIVE ALERT LOG --}}
            <div class="col-lg-4" id="alerts">

                <div class="tm-panel">

                    <div class="tm-panel-header">

                        <div>
                            <h5 class="tm-panel-title">
                                <i class="bi bi-bell-fill me-2"
                                   style="color:#ff7180;"></i>
                                Live Alert Log
                            </h5>

                            <div class="tm-panel-subtitle">
                                Latest threat records from database
                            </div>
                        </div>

                        <span class="tm-chart-tag">LATEST IOC</span>

                    </div>

                    <div style="max-height:355px;overflow-y:auto;">

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

                                    $tagList = is_array($decoded)
                                        ? $decoded
                                        : explode(',', $tagList);
                                }

                                if (!is_array($tagList)) {
                                    $tagList = [];
                                }
                            @endphp

                            <div class="tm-alert-item">

                                <div class="tm-alert-meta">

                                    <span class="tm-alert-type {{ $typeClass }}">
                                        {{ $type ?: 'UNKNOWN' }}
                                    </span>

                                    <span class="tm-alert-date">
                                        {{ $threat->date
                                            ? $threat->date->timezone('Asia/Jakarta')->format('d M, H:i')
                                            : '-' }}
                                    </span>

                                </div>

                                <div class="tm-alert-value">
                                    {{ $threat->value }}
                                </div>

                                <div class="tm-alert-user">
                                    <i class="bi bi-person me-1"></i>
                                    {{ $threat->user }}
                                    <span class="ms-2">
                                        {{ $threat->threat_id ?? 'TH-' . str_pad($threat->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>

                                @if(count($tagList))
                                    <div class="tm-alert-tags">
                                        @foreach(array_slice($tagList, 0, 4) as $tag)
                                            <span class="tm-alert-tag">
                                                #{{ ltrim(trim((string) $tag), '#') }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                            </div>

                        @empty

                            <div class="tm-empty">
                                No threat records available.
                            </div>

                        @endforelse

                    </div>

                    <div class="p-3 text-center"
                         style="border-top:1px solid var(--tm-border);">

                        <a href="{{ route('threatmetrics.index') }}"
                           style="color:var(--tm-cyan);font-size:11px;text-decoration:none;">

                            View All Threat Records
                            <i class="bi bi-arrow-right ms-1"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>


        {{-- IOC AND API CHARTS --}}
        <div class="row g-3" id="analytics">

            {{-- IOC DISTRIBUTION --}}
            <div class="col-lg-6">

                <div class="tm-panel">

                    <div class="tm-panel-header">

                        <div>
                            <h5 class="tm-panel-title">
                                <i class="bi bi-pie-chart-fill me-2"
                                   style="color:var(--tm-cyan);"></i>
                                IOC Type Distribution
                            </h5>

                            <div class="tm-panel-subtitle">
                                Distribution of threat indicator types
                            </div>
                        </div>

                        <span class="tm-chart-tag">IOC ANALYTICS</span>

                    </div>

                    <div class="tm-panel-body">

                        @if($typeCounts->isNotEmpty())

                            <div class="tm-chart-area small">
                                <canvas id="iocChart"></canvas>
                            </div>

                        @else

                            <div class="tm-empty">
                                No IOC data available for this period.
                            </div>

                        @endif

                    </div>

                </div>

            </div>


            {{-- API STATUS --}}
            <div class="col-lg-6">

                <div class="tm-panel">

                    <div class="tm-panel-header">

                        <div>
                            <h5 class="tm-panel-title">
                                <i class="bi bi-broadcast-pin me-2"
                                   style="color:var(--tm-cyan);"></i>
                                TweetFeed API Status
                            </h5>

                            <div class="tm-panel-subtitle">
                                Current API connection state
                            </div>
                        </div>

                        @if($apiStatus === 'online')
                            <span class="tm-api-status online">
                                <span class="tm-dot"></span> ONLINE
                            </span>
                        @elseif($apiStatus === 'offline')
                            <span class="tm-api-status offline">
                                <span class="tm-dot"></span> OFFLINE
                            </span>
                        @else
                            <span class="tm-api-status unknown">
                                <span class="tm-dot"></span> UNKNOWN
                            </span>
                        @endif

                    </div>

                    <div class="tm-panel-body">

                        @if($apiStatus !== 'unknown')

                            <div class="tm-chart-area small">
                                <canvas id="apiStatusChart"></canvas>
                            </div>

                        @else

                            <div class="tm-empty">
                                <div>
                                    <i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>
                                    API status data is not available yet.
                                    <br>
                                    Waiting for TweetFeed synchronization.
                                </div>
                            </div>

                        @endif

                        <div class="text-center mt-2"
                             style="font-size:11px;color:var(--tm-muted);">

                            Last successful synchronization:

                            <span style="color:#dce8f6;">

                                {{ $lastApiUpdate
                                    ? $lastApiUpdate->format('d M Y, H:i:s') . ' WIB'
                                    : 'No successful synchronization recorded' }}

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- FOOTER --}}
        <div class="tm-footer">
            THREATMETRICS CYBER THREAT INTELLIGENCE
            <br>
            Security Operations Center Dashboard
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

    // DATA DARI LARAVEL
    const trendLabels = @json($chartTrendLabels);
    const trendValues = @json($chartTrendValues);

    const iocLabels = @json($chartTypeLabels);
    const iocValues = @json($chartTypeValues);

    const apiValues = @json($apiStatusValues);


    // 1. THREAT ACTIVITY - LINE CHART
    const trendCanvas = document.getElementById('threatTrendChart');

    if (trendCanvas) {

        new Chart(trendCanvas, {
            type: 'line',

            data: {
                labels: trendLabels,

                datasets: [{
                    label: 'Threat Records',
                    data: trendValues,

                    borderColor: cyan,
                    backgroundColor: 'rgba(19,201,220,0.12)',

                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,

                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: cyan,
                    pointBorderColor: '#111a2a'
                }]
            },

            
            options: {
                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    intersect: false,
                    mode: 'index'
                },

                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Threats: ' + context.parsed.y;
                            }
                        }
                    }
                },

                scales: {
                    x: {
                        grid: {
                            display: false
                        },

                        ticks: {
                            color: muted,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },

                    y: {
                        beginAtZero: true,

                        ticks: {
                            precision: 0,
                            color: muted
                        },

                        grid: {
                            color: gridColor
                        }
                    }
                }
            }

        });
    }


    // 2. IOC DISTRIBUTION - DOUGHNUT CHART
    const iocCanvas = document.getElementById('iocChart');

    if (iocCanvas) {

        new Chart(iocCanvas, {
            type: 'doughnut',

            data: {
                labels: iocLabels,

                datasets: [{
                    data: iocValues,

                    backgroundColor: [
                        '#13c9dc',
                        '#268cf0',
                        '#37dfa5',
                        '#f5bd55',
                        '#a78bfa',
                        '#ff7180'
                    ],

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

                        labels: {
                            color: muted,
                            padding: 18,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    }


    // 3. API STATUS - DOUGHNUT CHART
    const apiCanvas = document.getElementById('apiStatusChart');

    if (apiCanvas) {

        new Chart(apiCanvas, {
            type: 'doughnut',

            data: {
                labels: ['Online', 'Offline'],

                datasets: [{
                    data: apiValues,

                    backgroundColor: [
                        '#37dfa5',
                        '#ff7180'
                    ],

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

                        labels: {
                            color: muted,
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' +
                                    (context.raw > 0
                                        ? 'Current state'
                                        : 'Inactive state');
                            }
                        }
                    }
                }
            }
        });
    }


    // 4. REAL-TIME CLOCK WIB
    function updateWibClock() {

        const now = new Date();

        const dateElement = document.getElementById('localDate');
        const timeElement = document.getElementById('localTime');

        if (dateElement) {
            dateElement.textContent =
                new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }).format(now);
        }

        if (timeElement) {
            timeElement.textContent =
                new Intl.DateTimeFormat('id-ID', {
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


    // 5. REFRESH TWEETFEED API
    const refreshForm = document.getElementById('refreshApiForm');
    const refreshButton = document.getElementById('refreshApiButton');
    const refreshIcon = document.getElementById('refreshApiIcon');
    const refreshText = document.getElementById('refreshApiText');
    const refreshNotice = document.getElementById('apiRefreshNotice');

    if (refreshForm) {

        refreshForm.addEventListener('submit', async function(event) {

            event.preventDefault();

            refreshButton.disabled = true;
            refreshIcon.classList.add('tm-spin');
            refreshText.textContent = 'Synchronizing...';

            refreshNotice.style.display = 'none';

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
                    throw new Error(
                        result.message || 'API synchronization failed.'
                    );
                }

                refreshNotice.className =
                    'tm-refresh-notice success';

                refreshNotice.style.display = 'block';

                refreshNotice.textContent =
                    result.message + ' Dashboard sedang diperbarui...';

                refreshText.textContent = 'Success!';

                // Muat ulang agar statistik dan timestamp terbaru tampil
                setTimeout(function() {
                    window.location.reload();
                }, 1000);

            } catch (error) {

                refreshNotice.className =
                    'tm-refresh-notice error';

                refreshNotice.style.display = 'block';

                refreshNotice.textContent =
                    error.message;

                refreshButton.disabled = false;
                refreshIcon.classList.remove('tm-spin');
                refreshText.textContent = 'Refresh API';

            }

        });

    }

});

</script>

@endpush
