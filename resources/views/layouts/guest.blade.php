<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Authentication') | ThreatMetrics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <style>
        :root {
            --auth-bg: #071521;
            --auth-panel: #0e2030;
            --auth-border: #1c3a4c;
            --auth-cyan: #13c9dc;
            --auth-muted: #8da5b8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 15%, #123449 0, transparent 35%),
                radial-gradient(circle at 85% 85%, #102d3d 0, transparent 35%),
                var(--auth-bg);
            color: #f3f8fc;
            font-family: Arial, Helvetica, sans-serif;
        }

        .auth-wrapper {
            min-height: 100vh;
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .auth-card {
            width: 100%;
            max-width: 1080px;
            min-height: 650px;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            background: var(--auth-panel);
            border: 1px solid var(--auth-border);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,.35);
        }

        .auth-visual {
            padding: 55px;
            background:
                linear-gradient(145deg, rgba(12,42,59,.96), rgba(7,21,33,.98));
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-visual::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .15;
            background-image:
                linear-gradient(rgba(19,201,220,.3) 1px, transparent 1px),
                linear-gradient(90deg, rgba(19,201,220,.3) 1px, transparent 1px);
            background-size: 38px 38px;
            mask-image: linear-gradient(to bottom right, black, transparent);
        }

        .auth-visual > * {
            position: relative;
            z-index: 1;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }

        .auth-brand-icon {
            width: 45px;
            height: 45px;
            border: 1px solid #1a8195;
            background: #103c50;
            color: var(--auth-cyan);
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 23px;
        }

        .auth-brand span {
            color: var(--auth-cyan);
        }

        .auth-hero h1 {
            font-size: clamp(32px, 4vw, 48px);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 20px;
        }

        .auth-hero h1 span {
            color: var(--auth-cyan);
        }

        .auth-hero p {
            color: #a4bacb;
            font-size: 14px;
            line-height: 1.8;
            max-width: 390px;
        }

        .auth-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 17px;
            color: #c5d6e2;
            font-size: 13px;
        }

        .auth-feature i {
            color: var(--auth-cyan);
            font-size: 17px;
        }

        .auth-visual-footer {
            color: #718da1;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .auth-panel {
            padding: 55px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-form {
            width: 100%;
            max-width: 390px;
            margin: auto;
        }

        .auth-heading {
            font-size: 29px;
            font-weight: 750;
            margin-bottom: 9px;
        }

        .auth-description {
            color: var(--auth-muted);
            font-size: 13px;
            margin-bottom: 32px;
        }

        .auth-label {
            color: #c2d2df;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .auth-input {
            height: 48px;
            background: #122b3b;
            border: 1px solid #25475a;
            border-radius: 9px;
            color: #fff;
            font-size: 13px;
        }

        .auth-input:focus {
            background: #122b3b;
            color: #fff;
            border-color: var(--auth-cyan);
            box-shadow: 0 0 0 3px rgba(19,201,220,.12);
        }

        .auth-input::placeholder {
            color: #718b9e;
        }

        .auth-password-wrap {
            position: relative;
        }

        .auth-password-wrap .auth-input {
            padding-right: 65px;
        }

        .auth-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: none;
            color: var(--auth-cyan);
            font-size: 11px;
            font-weight: 700;
        }

        .auth-button {
            width: 100%;
            height: 48px;
            background: var(--auth-cyan);
            border: 0;
            border-radius: 9px;
            color: #071521;
            font-size: 13px;
            font-weight: 800;
            transition: .2s;
        }

        .auth-button:hover {
            background: #55e3ed;
            transform: translateY(-1px);
        }

        .auth-link {
            color: var(--auth-cyan);
            text-decoration: none;
            font-weight: 700;
        }

        .auth-link:hover {
            color: #7beaf1;
        }

        .auth-divider {
            height: 1px;
            background: #244153;
            margin: 25px 0;
        }

        .auth-small {
            color: var(--auth-muted);
            font-size: 12px;
        }

        .auth-alert {
            font-size: 12px;
            border-radius: 9px;
        }

        .auth-error {
            color: #ff8995;
            font-size: 11px;
            margin-top: 6px;
        }

        @media(max-width: 800px) {
            .auth-wrapper {
                padding: 16px;
            }

            .auth-card {
                grid-template-columns: 1fr;
                max-width: 500px;
                min-height: auto;
            }

            .auth-visual {
                display: none;
            }

            .auth-panel {
                padding: 42px 28px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

<div class="auth-wrapper">

    <div class="auth-card">

        <aside class="auth-visual">

            <div class="auth-brand">
                <div class="auth-brand-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>

                <div>
                    THREAT<span>METRICS</span>
                </div>
            </div>

            <div class="auth-hero">

                <h1>
                    See Threats.<br>
                    <span>Stay Ahead.</span>
                </h1>

                <p>
                    Cyber Threat Intelligence Platform untuk
                    memantau, menganalisis, dan memahami
                    ancaman keamanan digital.
                </p>

                <div class="auth-feature">
                    <i class="bi bi-activity"></i>
                    Real-time Threat Monitoring
                </div>

                <div class="auth-feature">
                    <i class="bi bi-fingerprint"></i>
                    IOC Intelligence Analysis
                </div>

                <div class="auth-feature">
                    <i class="bi bi-shield-check"></i>
                    Secure Access Management
                </div>

            </div>

            <div class="auth-visual-footer">
                THREAT INTELLIGENCE · SECURITY OPERATIONS
            </div>

        </aside>

        <section class="auth-panel">

            <div class="auth-form">
                @yield('content')
            </div>

        </section>

    </div>

</div>

<script>
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.passwordToggle);

            if (!input) return;

            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            this.textContent = isPassword ? 'Hide' : 'Show';
        });
    });
</script>

@stack('scripts')

</body>
</html>
