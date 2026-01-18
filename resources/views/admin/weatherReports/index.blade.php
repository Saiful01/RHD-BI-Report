@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-cloud-line"></i>
        </span>
        Weather Analysis & Trend Report
    </h1>
</div>

<!-- Filter Card -->
<div class="filter-card mb-4">
    <div class="filter-header" id="toggle_filter">
        <div class="filter-header-left">
            <div class="filter-icon">
                <i class="ri-bar-chart-2-line"></i>
            </div>
            <div class="filter-header-text">
                <span class="filter-title">Report Parameters</span>
                <span class="filter-subtitle">Click to expand filters</span>
            </div>
        </div>
        <div class="filter-header-right">
            <span class="filter-badge" id="active_filters">No filters</span>
            <div class="filter-toggle-icon">
                <i class="ri-arrow-down-s-line"></i>
            </div>
        </div>
    </div>
    <div class="filter-body" id="filter_body" style="display: none;">
        <form action="{{ route('admin.weather-reports.index') }}" method="GET">
            <div class="filter-grid">
                <div class="filter-field" style="grid-column: span 2;">
                    <label class="filter-label">
                        <i class="ri-map-pin-line"></i>
                        Stations (Multiple)
                    </label>
                    <select name="station_ids[]" class="modern-select select2" multiple required>
                        @foreach($stations as $id => $name)
                            @php
                                $selectedStations = request('station_ids', []);
                                if (empty(request()->all())) {
                                    $isDefault = ($loop->iteration <= 2);
                                } else {
                                    $isDefault = in_array($id, $selectedStations);
                                }
                            @endphp
                            <option value="{{ $id }}" {{ $isDefault ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-calendar-line"></i>
                        From Date
                    </label>
                    <div class="input-wrapper">
                        <input type="date" name="from_date" class="modern-input"
                               value="{{ request('from_date', '2020-01-01') }}" required>
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-calendar-line"></i>
                        To Date
                    </label>
                    <div class="input-wrapper">
                        <input type="date" name="to_date" class="modern-input"
                               value="{{ request('to_date', '2025-01-01') }}" required>
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-line-chart-line"></i>
                        SD Type
                    </label>
                    <div class="select-wrapper">
                        <select name="sd_type" class="modern-select">
                            <option value="1" {{ request('sd_type', '1') == '1' ? 'selected' : '' }}>Population SD</option>
                            <option value="0" {{ request('sd_type') == '0' ? 'selected' : '' }}>Sample SD</option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-arrow-up-line"></i>
                        Max Count
                    </label>
                    <div class="input-wrapper">
                        <input type="number" name="max_avg_value" class="modern-input"
                               value="{{ request('max_avg_value', 7) }}">
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-arrow-down-line"></i>
                        Min Count
                    </label>
                    <div class="input-wrapper">
                        <input type="number" name="min_avg_value" class="modern-input"
                               value="{{ request('min_avg_value', 1) }}">
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="filter-btn filter-btn-primary">
                    <i class="ri-search-line"></i>
                    Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

@if(!empty($reportData))
    <div class="fluent-card">
        <div class="fluent-card-header">
            <h3 class="fluent-card-title">
                <i class="ri-bar-chart-2-line fluent-card-title-icon"></i>
                Temperature Analysis Results
            </h3>
        </div>
        <div class="fluent-card-body">
            <div class="fluent-table-wrapper">
                <table class="fluent-table text-center">
                    <thead>
                        <tr>
                            <th rowspan="3" class="align-middle">Location / Station</th>
                            <th colspan="4" class="align-middle">Air Temperature Statistics (°C)</th>
                            <th colspan="4" style="background-color: var(--fluent-warning-light);">50% Reliability Temperature (°C)</th>
                            <th colspan="4" style="background-color: var(--fluent-info-light);">98% Reliability Temperature (°C)</th>
                        </tr>
                        <tr>
                            <th colspan="2">High Temp</th>
                            <th colspan="2">Low Temp</th>
                            <th colspan="2" style="background-color: var(--fluent-warning-light);">Maximum</th>
                            <th colspan="2" style="background-color: var(--fluent-warning-light);">Minimum</th>
                            <th colspan="2" style="background-color: var(--fluent-info-light);">Maximum</th>
                            <th colspan="2" style="background-color: var(--fluent-info-light);">Minimum</th>
                        </tr>
                        <tr>
                            <th>AVG</th>
                            <th>STD</th>
                            <th>AVG</th>
                            <th>STD</th>
                            <th style="background-color: var(--fluent-warning-light);">AIR</th>
                            <th style="background-color: var(--fluent-warning-light);">PVT</th>
                            <th style="background-color: var(--fluent-warning-light);">AIR</th>
                            <th style="background-color: var(--fluent-warning-light);">PVT</th>
                            <th style="background-color: var(--fluent-info-light);">AIR</th>
                            <th style="background-color: var(--fluent-info-light);">PVT</th>
                            <th style="background-color: var(--fluent-info-light);">AIR</th>
                            <th style="background-color: var(--fluent-info-light);">PVT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData as $row)
                            <tr>
                                <td class="font-semibold text-left">
                                    <div class="d-flex align-center gap-2">
                                        <span class="fluent-stat-icon primary" style="width:24px;height:24px;font-size:12px;">
                                            <i class="ri-map-pin-line"></i>
                                        </span>
                                        {{ $row['station'] }}
                                    </div>
                                </td>
                                <td>{{ $row['high_avg'] }}</td>
                                <td>{{ $row['high_std'] }}</td>
                                <td>{{ $row['low_avg'] }}</td>
                                <td>{{ $row['low_std'] }}</td>
                                <td style="background-color: var(--fluent-warning-light);">{{ $row['rel50']['max_air'] }}</td>
                                <td style="background-color: var(--fluent-warning-light);">{{ $row['rel50']['max_pvt'] }}</td>
                                <td style="background-color: var(--fluent-warning-light);">{{ $row['rel50']['min_air'] }}</td>
                                <td style="background-color: var(--fluent-warning-light);">{{ $row['rel50']['min_pvt'] }}</td>
                                <td style="background-color: var(--fluent-info-light);">{{ $row['rel98']['max_air'] }}</td>
                                <td style="background-color: var(--fluent-info-light);">{{ $row['rel98']['max_pvt'] }}</td>
                                <td style="background-color: var(--fluent-info-light);">{{ $row['rel98']['min_air'] }}</td>
                                <td style="background-color: var(--fluent-info-light);">{{ $row['rel98']['min_pvt'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection

@section('styles')
<style>
/* Filter Card */
.filter-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
}
.filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    cursor: pointer;
    transition: background 0.15s ease;
    user-select: none;
}
.filter-header:hover {
    background: var(--fluent-gray-10);
}
.filter-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.filter-icon {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}
.filter-header-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.filter-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--fluent-text-primary);
}
.filter-subtitle {
    font-size: 12px;
    color: var(--fluent-text-secondary);
}
.filter-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.filter-badge {
    padding: 4px 10px;
    background: var(--fluent-gray-20);
    border-radius: 20px;
    font-size: 12px;
    color: var(--fluent-text-secondary);
}
.filter-badge.active {
    background: rgba(0, 120, 212, 0.12);
    color: var(--fluent-primary);
    font-weight: 500;
}
.filter-toggle-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--fluent-radius-sm);
    background: var(--fluent-gray-20);
    color: var(--fluent-text-secondary);
    font-size: 20px;
    transition: transform 0.2s ease, background 0.15s ease;
}
.filter-header.expanded .filter-toggle-icon {
    transform: rotate(180deg);
}
.filter-body {
    padding: 20px;
    border-top: 1px solid var(--fluent-gray-20);
    background: var(--fluent-gray-10);
}
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}
.filter-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.filter-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    color: var(--fluent-text-secondary);
}
.filter-label i {
    color: var(--fluent-primary);
    font-size: 14px;
}
.select-wrapper {
    position: relative;
}
.modern-select {
    width: 100%;
    padding: 12px 40px 12px 14px;
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    font-size: 14px;
    appearance: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.modern-select:hover {
    border-color: var(--fluent-gray-50);
}
.modern-select:focus {
    outline: none;
    border-color: var(--fluent-primary);
    box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.15);
}
.select-arrow {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--fluent-text-secondary);
    pointer-events: none;
    font-size: 16px;
}
.input-wrapper {
    position: relative;
}
.modern-input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    font-size: 14px;
    transition: all 0.15s ease;
}
.modern-input:hover {
    border-color: var(--fluent-gray-50);
}
.modern-input:focus {
    outline: none;
    border-color: var(--fluent-primary);
    box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.15);
}
.modern-input[type="date"] {
    color-scheme: light;
}
.filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid var(--fluent-gray-20);
}
.filter-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: var(--fluent-radius-md);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.15s ease;
}
.filter-btn-primary {
    background: var(--fluent-primary);
    color: white;
}
.filter-btn-primary:hover {
    background: #106EBE;
}
</style>
@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    // Filter toggle functionality
    $('#toggle_filter').on('click', function() {
        const $filterBody = $('#filter_body');
        const $header = $(this);
        const isExpanded = $filterBody.is(':visible');

        $filterBody.slideToggle(250);
        $header.toggleClass('expanded', !isExpanded);

        const subtitle = $header.find('.filter-subtitle');
        subtitle.text(isExpanded ? 'Click to expand filters' : 'Click to collapse filters');
    });
    const ctx = document.getElementById('weatherTrendChart');
    if(ctx) {
        const trendData = {!! json_encode($trendData ?? []) !!};
        const datasets = [];
        const colors = ['#0078D4', '#D13438', '#107C10', '#FFB900', '#9966FF', '#4BC0C0', '#FF9800'];
        let colorIndex = 0;

        for (const [station, data] of Object.entries(trendData)) {
            datasets.push({
                label: station,
                data: data.map(item => ({x: item.t, y: item.y})),
                borderColor: colors[colorIndex % colors.length],
                backgroundColor: colors[colorIndex % colors.length],
                fill: false,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 5
            });
            colorIndex++;
        }

        new Chart(ctx, {
            type: 'line',
            data: { datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        type: 'category',
                        title: { display: true, text: 'Date' },
                        ticks: {
                            maxRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 20
                        }
                    },
                    y: {
                        title: { display: true, text: 'Temperature (°C)' },
                        beginAtZero: false
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { enabled: true }
                }
            }
        });
    }
});
</script>
@endsection
