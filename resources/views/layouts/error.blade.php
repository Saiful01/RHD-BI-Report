<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Error') - {{ trans('panel.site_title') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Fluent UI Theme -->
    <link href="{{ asset('css/fluent-theme.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/error-pages.css') }}" rel="stylesheet" />
</head>

<body>
    <div class="error-page">
        <!-- Floating Vehicles -->
        <div class="floating-vehicles">
            <div class="vehicle"><i class="ri-truck-line"></i></div>
            <div class="vehicle"><i class="ri-bus-line"></i></div>
            <div class="vehicle"><i class="ri-car-line"></i></div>
            <div class="vehicle"><i class="ri-truck-line"></i></div>
            <div class="vehicle"><i class="ri-roadster-line"></i></div>
        </div>

        <!-- Road Markings -->
        <div class="road-markings">
            <div class="road-line"></div>
            <div class="road-line"></div>
            <div class="road-line"></div>
            <div class="road-line"></div>
            <div class="road-line"></div>
        </div>

        <div class="error-container">
            <div class="error-card">
                <div class="error-header @yield('header-class', '')">
                    <div class="error-icon @yield('icon-class', '')">
                        @yield('icon')
                    </div>
                    <div class="error-code">@yield('code')</div>
                </div>
                <div class="error-body">
                    <h1 class="error-title">@yield('error-title')</h1>
                    <p class="error-message">@yield('message')</p>
                    @yield('extra-content')
                    <div class="error-actions">
                        @yield('actions')
                    </div>
                </div>
            </div>
        </div>

        <!-- RHD Badge -->
        <div class="rhd-badge">
            <i class="ri-road-map-line"></i> ROADS & HIGHWAYS DIVISION
        </div>
    </div>

    <script>
        // Simple back navigation
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }
    </script>
    @yield('scripts')
</body>

</html>
