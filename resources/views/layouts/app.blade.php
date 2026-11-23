<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'ThreatMetrics')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <style>
        :root {
            --bg: #17232f;
            --panel: #1e2b37;
            --panel-header: #263543;
            --panel-hover: #22313e;
            --border: #344453;
            --text: #f0f4f8;
            --muted: #8da4bb;
            --blue: #159be8;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
        }

        body {
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        /* MAIN CONTENT */
        .main-content {
            width: 100%;
            min-height: 100vh;
            padding: 24px 40px;
            background: var(--bg);
        }

        /* PANEL */
        .card {
            background: var(--panel) !important;
            color: var(--text) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
        }

        .card-header {
            background: var(--panel-header) !important;
            color: var(--text) !important;
            border-bottom: 1px solid var(--border) !important;
        }

        .card-body {
            color: var(--text);
        }

        /* FORM */
        .form-label {
            color: var(--muted);
            font-weight: 600;
            font-size: 13px;
        }

        .form-control,
        .form-select {
            background-color: var(--panel-header) !important;
            color: var(--text) !important;
            border: 1px solid var(--border) !important;
            border-radius: 7px;
        }

        .form-control::placeholder {
            color: var(--muted) !important;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--panel-header) !important;
            color: var(--text) !important;
            border-color: var(--blue) !important;
            box-shadow: 0 0 0 3px rgba(21,155,232,.15) !important;
        }

        .form-control:disabled {
            background-color: var(--panel-header) !important;
            color: var(--muted) !important;
        }

        /* TABLE */
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text);
            --bs-table-border-color: var(--border);
            --bs-table-hover-bg: var(--panel-hover);
            --bs-table-hover-color: #fff;
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--panel-header);
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table tbody td {
            color: var(--text);
            vertical-align: middle;
        }

        /* TEXT */
        .text-secondary {
            color: var(--muted) !important;
        }

        /* BUTTON */
        .btn-primary {
            background: var(--blue) !important;
            border-color: var(--blue) !important;
        }

        .btn-primary:hover {
            background: #087fc5 !important;
            border-color: #087fc5 !important;
        }

        /* PAGINATION */
        .pagination {
            --bs-pagination-bg: var(--panel);
            --bs-pagination-color: var(--muted);
            --bs-pagination-border-color: var(--border);
            --bs-pagination-hover-bg: var(--panel-header);
            --bs-pagination-hover-color: #fff;
            --bs-pagination-hover-border-color: var(--border);
            --bs-pagination-active-bg: var(--blue);
            --bs-pagination-active-border-color: var(--blue);
            --bs-pagination-disabled-bg: var(--panel);
            --bs-pagination-disabled-color: var(--muted);
            --bs-pagination-disabled-border-color: var(--border);
        }

        .alert {
            border-radius: 8px;
        }

        hr {
            border-color: var(--border);
            opacity: 1;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px 14px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <main class="main-content">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')

</body>
</html>
