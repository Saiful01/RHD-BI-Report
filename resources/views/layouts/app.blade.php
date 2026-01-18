<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', trans('panel.site_title'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Fluent UI Theme -->
    <link href="{{ asset('css/fluent-theme.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-components.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-utilities.css') }}" rel="stylesheet" />

    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--fluent-space-lg);
            position: relative;
            overflow: hidden;
        }

        /* Animated Highway Background - Toll Plaza */
        .auth-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 58, 95, 0.75) 100%),
                url('https://images.unsplash.com/photo-1767851522846-9740ecd09cea?w=1920&q=80&fit=crop');
            background-size: cover;
            background-position: center;
            animation: slowPan 30s ease-in-out infinite alternate;
            z-index: -2;
        }

        /* Moving Road Lines Effect */
        .auth-page::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 50px,
                    rgba(255, 200, 0, 0.03) 50px,
                    rgba(255, 200, 0, 0.03) 52px
                );
            animation: roadLines 20s linear infinite;
            z-index: -1;
        }

        @keyframes slowPan {
            0% {
                background-position: 0% 30%;
                transform: scale(1);
            }
            50% {
                background-position: 100% 50%;
                transform: scale(1.05);
            }
            100% {
                background-position: 50% 70%;
                transform: scale(1);
            }
        }

        @keyframes roadLines {
            0% {
                transform: translateX(-100px);
            }
            100% {
                transform: translateX(100px);
            }
        }

        /* Floating Vehicles Animation */
        .floating-vehicles {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }

        .vehicle {
            position: absolute;
            font-size: 24px;
            opacity: 0.15;
            animation: floatVehicle linear infinite;
        }

        .vehicle:nth-child(1) { top: 10%; left: -50px; animation-duration: 25s; animation-delay: 0s; }
        .vehicle:nth-child(2) { top: 30%; left: -50px; animation-duration: 20s; animation-delay: 5s; }
        .vehicle:nth-child(3) { top: 50%; left: -50px; animation-duration: 22s; animation-delay: 10s; }
        .vehicle:nth-child(4) { top: 70%; left: -50px; animation-duration: 28s; animation-delay: 3s; }
        .vehicle:nth-child(5) { top: 85%; left: -50px; animation-duration: 18s; animation-delay: 8s; }

        @keyframes floatVehicle {
            0% {
                transform: translateX(-50px);
                opacity: 0;
            }
            10% {
                opacity: 0.15;
            }
            90% {
                opacity: 0.15;
            }
            100% {
                transform: translateX(calc(100vw + 50px));
                opacity: 0;
            }
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--fluent-radius-xl);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-header {
            text-align: center;
            padding: var(--fluent-space-xl) var(--fluent-space-lg) var(--fluent-space-lg);
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,200,0,0.1) 0%, transparent 50%);
            animation: headerGlow 10s ease-in-out infinite;
        }

        @keyframes headerGlow {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, 20px); }
        }

        .auth-logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: var(--fluent-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--fluent-space-md);
            color: white;
            font-size: 32px;
            position: relative;
            box-shadow: 0 10px 30px -5px rgba(245, 158, 11, 0.5);
        }

        .auth-title {
            color: var(--fluent-white);
            font-size: var(--fluent-font-size-xl);
            font-weight: var(--fluent-font-weight-bold);
            margin: 0;
            position: relative;
        }

        .auth-subtitle {
            color: rgba(255,255,255,0.8);
            font-size: var(--fluent-font-size-sm);
            margin-top: var(--fluent-space-xs);
            position: relative;
        }

        .auth-body {
            padding: var(--fluent-space-xl) var(--fluent-space-lg);
        }

        .auth-footer {
            text-align: center;
            padding: var(--fluent-space-md) var(--fluent-space-lg) var(--fluent-space-lg);
            border-top: 1px solid var(--fluent-gray-30);
        }

        /* RHD Badge */
        .rhd-badge {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 30px;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            letter-spacing: 1px;
            border: 1px solid rgba(255,255,255,0.1);
        }
    </style>

    @yield('styles')
</head>

<body>
    <!-- Page Loader -->
    <div class="fluent-page-loader" id="pageLoader">
        <div class="fluent-loader-content">
            <div class="fluent-loader-spinner">
                <div class="fluent-loader-ring"></div>
                <div class="fluent-loader-ring"></div>
                <div class="fluent-loader-ring"></div>
                <svg class="fluent-loader-logo" width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="12" width="4" height="9" rx="1" fill="#0078D4"/>
                    <rect x="10" y="8" width="4" height="13" rx="1" fill="#00BCF2"/>
                    <rect x="17" y="3" width="4" height="18" rx="1" fill="#0078D4"/>
                </svg>
            </div>
            <div class="fluent-loader-text">Loading...</div>
        </div>
    </div>

    <div class="auth-page">
        <!-- Floating Vehicles -->
        <div class="floating-vehicles">
            <div class="vehicle"><i class="ri-truck-line"></i></div>
            <div class="vehicle"><i class="ri-bus-line"></i></div>
            <div class="vehicle"><i class="ri-car-line"></i></div>
            <div class="vehicle"><i class="ri-truck-line"></i></div>
            <div class="vehicle"><i class="ri-roadster-line"></i></div>
        </div>

        <div class="auth-container">
            @yield("content")
        </div>

        <!-- RHD Badge -->
        <div class="rhd-badge">
            <i class="ri-road-map-line"></i> ROADS & HIGHWAYS DIVISION
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="{{ asset('js/fluent-theme.js') }}"></script>
    @yield('scripts')
</body>

</html>
