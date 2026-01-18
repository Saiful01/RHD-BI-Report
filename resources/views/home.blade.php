@extends('layouts.admin')
@section('title', 'Dashboard - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-dashboard-3-line"></i>
        </span>
        Dashboard
    </h1>
</div>

<!-- Stats Grid -->
<div class="fluent-stats-grid">
    <div class="fluent-stat-card">
        <div class="fluent-stat-icon primary">
            <i class="ri-user-line"></i>
        </div>
        <div class="fluent-stat-content">
            <div class="fluent-stat-value">{{ \App\Models\User::count() }}</div>
            <div class="fluent-stat-label">Total Users</div>
        </div>
    </div>

    <div class="fluent-stat-card">
        <div class="fluent-stat-icon success">
            <i class="ri-map-pin-line"></i>
        </div>
        <div class="fluent-stat-content">
            <div class="fluent-stat-value">{{ \App\Models\Station::count() }}</div>
            <div class="fluent-stat-label">Weather Stations</div>
        </div>
    </div>

    <div class="fluent-stat-card">
        <div class="fluent-stat-icon warning">
            <i class="ri-file-list-3-line"></i>
        </div>
        <div class="fluent-stat-content">
            <div class="fluent-stat-value">{{ \App\Models\Tender::count() }}</div>
            <div class="fluent-stat-label">Total Tenders</div>
        </div>
    </div>

    <div class="fluent-stat-card">
        <div class="fluent-stat-icon danger">
            <i class="ri-sun-cloudy-line"></i>
        </div>
        <div class="fluent-stat-content">
            <div class="fluent-stat-value">{{ \App\Models\DailyWeather::count() }}</div>
            <div class="fluent-stat-label">Weather Records</div>
        </div>
    </div>
</div>

<!-- Welcome Card -->
<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-hand-heart-line fluent-card-title-icon"></i>
            Welcome Back!
        </h3>
    </div>
    <div class="fluent-card-body">
        @if(session('status'))
            <div class="fluent-alert fluent-alert-success mb-4">
                <span class="fluent-alert-icon">
                    <i class="ri-check-line"></i>
                </span>
                <div class="fluent-alert-content">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <div class="d-flex align-center gap-4">
            <div class="fluent-user-avatar" style="width: 64px; height: 64px; font-size: 24px;">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <h3 class="mb-1">Hello, {{ auth()->user()->name ?? 'User' }}!</h3>
                <p class="text-secondary mb-0">You are logged in to the RHD BI Report system.</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="fluent-card mt-4">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-rocket-line fluent-card-title-icon"></i>
            Quick Actions
        </h3>
    </div>
    <div class="fluent-card-body">
        <div class="d-flex flex-wrap gap-3">
            @can('user_access')
            <a href="{{ route('admin.users.index') }}" class="fluent-btn fluent-btn-secondary">
                <i class="ri-user-line"></i>
                Manage Users
            </a>
            @endcan
            @can('station_access')
            <a href="{{ route('admin.stations.index') }}" class="fluent-btn fluent-btn-secondary">
                <i class="ri-map-pin-line"></i>
                View Stations
            </a>
            @endcan
            @can('tender_access')
            <a href="{{ route('admin.tender.index') }}" class="fluent-btn fluent-btn-secondary">
                <i class="ri-file-list-3-line"></i>
                Browse Tenders
            </a>
            @endcan
            @can('weather_report_access')
            <a href="{{ route('admin.weather-reports.index') }}" class="fluent-btn fluent-btn-secondary">
                <i class="ri-cloud-line"></i>
                Weather Reports
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
@endsection
