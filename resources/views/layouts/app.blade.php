<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ThreatMetrics')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #0b1120;
            color: #f2f6fc;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .tm-top-nav {
            background: #111a2a;
            border-bottom: 1px solid #26364b;
            padding: 12px 28px;
        }
        .tm-brand {
            color: #ffffff;
            font-weight: 800;
            letter-spacing: 1px;
            text-decoration: none;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .tm-brand i {
            color: #13c9dc;
        }
        .tm-nav-link {
            color: #9fb1c7;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: .15s;
        }
        .tm-nav-link:hover,
        .tm-nav-link.active {
            background: #13c9dc;
            color: #0b1120;
        }
        .tm-main-wrap {
            padding: 24px 36px;
        }
        @media (max-width: 768px) {
            .tm-top-nav {
                padding: 12px 14px;
            }
            .tm-main-wrap {
                padding: 16px 14px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="tm-top-nav d-flex justify-content-between align-items-center flex-wrap gap-3">
        <a href="{{ route('dashboard') }}" class="tm-brand">
            <i class="bi bi-shield-check"></i>
            THREATMETRICS
        </a>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('dashboard') }}" class="tm-nav-link active">
                <i class="bi bi-grid"></i>
                Dashboard
            </a>
            <a href="#analytics" class="tm-nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Analytics
            </a>
            <a href="#alerts" class="tm-nav-link">
                <i class="bi bi-bell"></i>
                Alerts
            </a>
            <a href="#ioc-data" class="tm-nav-link">
                <i class="bi bi-database"></i>
                IOC Data
            </a>
        </div>

        <form id="refreshApiForm" action="{{ route('tweetfeed.sync') }}" method="POST" class="m-0">
            @csrf
            <input type="hidden" name="range" value="month">
            <button type="submit" id="refreshApiButton" class="btn btn-sm btn-outline-info rounded-pill px-3">
                <i id="refreshApiIcon" class="bi bi-arrow-repeat me-1"></i>
                <span id="refreshApiText">Sync API</span>
            </button>
        </form>
    </nav>

    <main class="tm-main-wrap">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>