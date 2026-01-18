@extends('layouts.admin')
@section('title', 'Daily Weather - ' . trans('panel.site_title'))
@section('content')
<style>
/* Prevent horizontal overflow */
.view-section {
    overflow-x: hidden;
    max-width: 100%;
}
.fluent-page-header {
    flex-wrap: wrap;
}
</style>
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-sun-cloudy-line"></i>
        </span>
        Weather Analytics Dashboard
    </h1>
    <div class="fluent-page-actions">
        <div class="view-mode-toggle">
            <button type="button" class="mode-btn active" data-mode="stationAnalytics" title="Station Analytics">
                <i class="ri-bar-chart-box-line"></i>
            </button>
            <button type="button" class="mode-btn" data-mode="calendar" title="Calendar Heatmap">
                <i class="ri-calendar-line"></i>
            </button>
            <button type="button" class="mode-btn" data-mode="records" title="Records & Extremes">
                <i class="ri-trophy-line"></i>
            </button>
            <button type="button" class="mode-btn" data-mode="data" title="Data List">
                <i class="ri-table-line"></i>
            </button>
            <button type="button" class="mode-btn" data-mode="stationAnalysis" title="Station Comparison & Analysis">
                <i class="ri-bar-chart-grouped-line"></i>
            </button>
        </div>
        @can('daily_weather_create')
            <button type="button" class="fluent-btn fluent-btn-primary" onclick="openCreateModal()">
                <i class="ri-add-line"></i>
                Add Record
            </button>
        @endcan
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
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
        <p>Loading weather data...</p>
    </div>
</div>

<!-- ==================== STATION ANALYTICS VIEW ==================== -->
<div id="stationAnalyticsView" class="view-section">
    <div class="analytics-controls">
        <div class="control-group">
            <label>Select Station:</label>
            <select id="analyticsStation" class="fluent-select">
                @foreach($stations as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="view-toggle" id="analyticsViewToggle">
            <button type="button" class="view-btn" data-view="month">Month</button>
            <button type="button" class="view-btn active" data-view="year">Year</button>
            <button type="button" class="view-btn" data-view="decade">Decade</button>
        </div>
        <div class="date-controls" id="analyticsDateControls">
            <select id="analyticsYear" class="fluent-select">
                @for($y = date('Y'); $y >= 1990; $y--)
                    <option value="{{ $y }}" {{ $y == 2025 ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select id="analyticsMonth" class="fluent-select" style="display: none;">
                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                    <option value="{{ $i + 1 }}" {{ ($i + 1) == date('n') ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <div id="decadeRangeControls" class="decade-range-controls" style="display: none;">
                <select id="decadeStart" class="fluent-select">
                    @for($d = 2020; $d >= 1950; $d -= 10)
                        <option value="{{ $d }}" {{ $d == 2010 ? 'selected' : '' }}>{{ $d }}s</option>
                    @endfor
                </select>
                <span class="range-separator">to</span>
                <select id="decadeEnd" class="fluent-select">
                    @for($d = 2020; $d >= 1950; $d -= 10)
                        <option value="{{ $d }}" {{ $d == 2020 ? 'selected' : '' }}>{{ $d }}s</option>
                    @endfor
                </select>
            </div>
        </div>
        <button type="button" class="fluent-btn fluent-btn-primary" onclick="loadStationAnalytics()">
            <i class="ri-refresh-line"></i> Load Data
        </button>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row" id="analyticsStats"></div>

    <!-- Charts Grid -->
    <div class="charts-container" id="analyticsChartsGrid">
        <div class="chart-card">
            <div class="chart-header"><h4><i class="ri-temp-hot-line"></i> Temperature Overview</h4></div>
            <div class="chart-body"><canvas id="analyticsTempChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-header"><h4><i class="ri-rainy-line"></i> Rainfall</h4></div>
            <div class="chart-body"><canvas id="analyticsRainChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-header"><h4><i class="ri-sun-line"></i> Sunshine Hours</h4></div>
            <div class="chart-body"><canvas id="analyticsSunshineChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-header"><h4><i class="ri-drop-line"></i> Humidity</h4></div>
            <div class="chart-body"><canvas id="analyticsHumidityChart"></canvas></div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="chart-card full-width">
        <div class="chart-header"><h4><i class="ri-table-line"></i> Detailed Data</h4></div>
        <div class="chart-body">
            <div class="fluent-table-container">
                <table class="fluent-table" id="analyticsDataTable">
                    <thead id="analyticsTableHead"></thead>
                    <tbody id="analyticsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==================== CALENDAR HEATMAP VIEW ==================== -->
<div id="calendarView" class="view-section" style="display: none;">
    <div class="calendar-controls">
        <div class="control-group">
            <label>Station:</label>
            <select id="calendarStation" class="fluent-select">
                <option value="">All Stations (Average)</option>
                @foreach($stations as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="control-group">
            <label>Year:</label>
            <select id="calendarYear" class="fluent-select"></select>
        </div>
        <div class="control-group">
            <label>Metric:</label>
            <select id="calendarMetric" class="fluent-select">
                <option value="max_temp">Max Temperature</option>
                <option value="min_temp">Min Temperature</option>
                <option value="rainfall">Rainfall</option>
                <option value="humidity">Humidity</option>
            </select>
        </div>
    </div>

    <div class="calendar-legend" id="calendarLegend"></div>
    <div class="calendar-heatmap" id="calendarHeatmap"></div>
    <div class="calendar-tooltip" id="calendarTooltip"></div>
</div>


<!-- ==================== RECORDS VIEW ==================== -->
<div id="recordsView" class="view-section" style="display: none;">
    <div class="records-controls">
        <div class="control-group">
            <label>Station:</label>
            <select id="recordsStation" class="fluent-select">
                <option value="">All Stations</option>
                @foreach($stations as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" class="fluent-btn fluent-btn-primary" onclick="loadRecords()">
            <i class="ri-refresh-line"></i> Load Records
        </button>
    </div>

    <!-- Extreme Records -->
    <div class="section-header">
        <h3><i class="ri-trophy-line"></i> All-Time Records</h3>
    </div>
    <div class="extreme-records" id="extremeRecords"></div>

    <!-- Monthly Averages Chart -->
    <div class="chart-section">
        <div class="section-header">
            <h3><i class="ri-bar-chart-line"></i> Monthly Temperature Records</h3>
        </div>
        <div class="chart-container">
            <canvas id="monthlyRecordsChart"></canvas>
        </div>
    </div>

    <!-- Yearly Trends Chart -->
    <div class="chart-section">
        <div class="section-header">
            <h3><i class="ri-line-chart-line"></i> Yearly Temperature Trends</h3>
        </div>
        <div class="chart-container">
            <canvas id="yearlyTrendsChart"></canvas>
        </div>
    </div>
</div>

<!-- ==================== DATA LIST VIEW ==================== -->
<div id="dataView" class="view-section" style="display: none;">
    <!-- Filter Card -->
    <div class="filter-card">
        <div class="filter-header" id="toggle_filter">
            <div class="filter-header-left">
                <div class="filter-icon"><i class="ri-filter-3-line"></i></div>
                <div class="filter-header-text">
                    <span class="filter-title">Filter & Export</span>
                    <span class="filter-subtitle">Click to expand</span>
                </div>
            </div>
            <div class="filter-header-right">
                <span class="filter-badge" id="active_filters">No filters</span>
                <div class="filter-toggle-icon"><i class="ri-arrow-down-s-line"></i></div>
            </div>
        </div>
        <div class="filter-body" id="filter_body" style="display: none;">
            <div class="filter-grid">
                <div class="filter-field">
                    <label class="filter-label"><i class="ri-map-pin-line"></i> Station</label>
                    <select id="station_id" class="fluent-select">
                        <option value="">All Stations</option>
                        @foreach($stations as $id => $entry)
                            <option value="{{ $id }}">{{ $entry }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label"><i class="ri-calendar-line"></i> From Date</label>
                    <input type="date" id="from_date" class="fluent-input">
                </div>
                <div class="filter-field">
                    <label class="filter-label"><i class="ri-calendar-line"></i> To Date</label>
                    <input type="date" id="to_date" class="fluent-input">
                </div>
            </div>
            <div class="filter-actions">
                <button type="button" id="export_csv" class="fluent-btn fluent-btn-secondary">
                    <i class="ri-download-line"></i> Export CSV
                </button>
                <button type="button" id="reset_button" class="fluent-btn fluent-btn-secondary">
                    <i class="ri-refresh-line"></i> Reset
                </button>
                <button type="button" id="filter_button" class="fluent-btn fluent-btn-primary">
                    <i class="ri-search-line"></i> Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Results Header -->
    <div class="results-header">
        <span id="results_count" class="results-count">Loading...</span>
        <div class="view-toggle-btns">
            <button type="button" class="toggle-btn active" data-view="cards"><i class="ri-layout-grid-line"></i></button>
            <button type="button" class="toggle-btn" data-view="table"><i class="ri-table-line"></i></button>
        </div>
        <div class="results-pagination" id="pagination_top"></div>
    </div>

    <!-- Loading State -->
    <div id="loading_state" class="loading-card" style="display: none;">
        <div class="loading-content">
            <div class="fluent-loader-spinner" style="transform: scale(0.8);">
                <div class="fluent-loader-ring"></div>
                <div class="fluent-loader-ring"></div>
                <div class="fluent-loader-ring"></div>
                <svg class="fluent-loader-logo" width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="12" width="4" height="9" rx="1" fill="#0078D4"/>
                    <rect x="10" y="8" width="4" height="13" rx="1" fill="#00BCF2"/>
                    <rect x="17" y="3" width="4" height="18" rx="1" fill="#0078D4"/>
                </svg>
            </div>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Empty State -->
    <div id="empty_state" class="empty-card" style="display: none;">
        <div class="empty-content">
            <div class="empty-icon"><i class="ri-cloud-off-line"></i></div>
            <h3>No Weather Records Found</h3>
            <p>No weather data matches your filter criteria.</p>
        </div>
    </div>

    <!-- Cards View -->
    <div id="weather_cards" class="cards-grid"></div>

    <!-- Table View -->
    <div id="weather_table_container" class="table-container" style="display: none;">
        <table class="data-table" id="weather_table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Station</th>
                    <th>Max Temp</th>
                    <th>Min Temp</th>
                    <th>Humidity</th>
                    <th>Rainfall</th>
                    <th>Sunshine</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="weather_table_body"></tbody>
        </table>
    </div>

    <div class="results-footer">
        <div class="results-pagination" id="pagination_bottom"></div>
    </div>
</div>

<!-- ==================== STATION ANALYSIS VIEW ==================== -->
<div id="stationAnalysisView" class="view-section" style="display: none;">
    <!-- Filter Card -->
    <div class="analysis-filter-card">
        <div class="analysis-filter-header">
            <div class="analysis-filter-title">
                <div class="analysis-title-icon">
                    <i class="ri-bar-chart-grouped-line"></i>
                </div>
                <div>
                    <h2>Station Comparison & Analysis</h2>
                    <p>Compare stations and view statistical analysis with reliability data</p>
                </div>
            </div>
            <button type="button" id="runAnalysis" class="fluent-btn fluent-btn-light fluent-btn-lg">
                <i class="ri-play-circle-line"></i> Run Analysis
            </button>
        </div>

        <div class="analysis-filter-body">
            <!-- Station Selection -->
            <div class="filter-section">
                <div class="filter-section-header">
                    <h4><i class="ri-map-pin-line"></i> Select Stations (up to 6)</h4>
                    <div class="station-quick-actions">
                        <button type="button" class="quick-action-btn" onclick="selectAllAnalysisStations()">
                            <i class="ri-checkbox-multiple-line"></i> Select All
                        </button>
                        <button type="button" class="quick-action-btn" onclick="deselectAllAnalysisStations()">
                            <i class="ri-checkbox-blank-line"></i> Deselect All
                        </button>
                    </div>
                </div>
                <div class="station-chips-grid" id="analysisStationGrid">
                    @foreach($stations as $id => $name)
                    <label class="station-chip">
                        <input type="checkbox" name="analysis_stations[]" value="{{ $id }}" class="analysis-station-cb">
                        <span class="chip-content">
                            <i class="ri-map-pin-2-line"></i>
                            <span class="chip-label">{{ $name }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Parameters Grid -->
            <div class="params-grid">
                <div class="param-card">
                    <div class="param-icon param-date"><i class="ri-calendar-event-line"></i></div>
                    <div class="param-content">
                        <label>From Date</label>
                        <input type="date" id="analysisFromDate" class="fluent-input" value="{{ (date('Y') - 1) }}-01-01">
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon param-date"><i class="ri-calendar-check-line"></i></div>
                    <div class="param-content">
                        <label>To Date</label>
                        <input type="date" id="analysisToDate" class="fluent-input" value="{{ (date('Y') - 1) }}-12-31">
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon high"><i class="ri-arrow-up-circle-line"></i></div>
                    <div class="param-content">
                        <label>Top High Temps</label>
                        <input type="number" id="analysisMaxCount" class="fluent-input" value="10" min="1" max="100">
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon low"><i class="ri-arrow-down-circle-line"></i></div>
                    <div class="param-content">
                        <label>Top Low Temps</label>
                        <input type="number" id="analysisMinCount" class="fluent-input" value="10" min="1" max="100">
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon calc"><i class="ri-calculator-line"></i></div>
                    <div class="param-content">
                        <label>SD Type</label>
                        <select id="analysisSdType" class="fluent-select">
                            <option value="population">Population SD</option>
                            <option value="sample">Sample SD</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Results -->
    <div id="analysisResults" style="display: none;">
        <!-- Summary Cards -->
        <div class="analysis-summary-grid" id="analysisSummary"></div>

        <!-- Comparison Charts -->
        <div class="analysis-charts-grid">
            <div class="analysis-chart-card">
                <div class="chart-card-header">
                    <h4><i class="ri-temp-hot-line"></i> Temperature Comparison</h4>
                </div>
                <div class="chart-card-body">
                    <canvas id="analysisTempChart"></canvas>
                </div>
            </div>
            <div class="analysis-chart-card">
                <div class="chart-card-header">
                    <h4><i class="ri-rainy-line"></i> Rainfall Comparison</h4>
                </div>
                <div class="chart-card-body">
                    <canvas id="analysisRainChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Comparison Table -->
        <div class="analysis-table-card comparison-section">
            <div class="table-card-header">
                <div class="header-left">
                    <i class="ri-git-compare-line"></i>
                    <h3>Quick Comparison</h3>
                </div>
            </div>
            <div class="table-card-body">
                <table class="analysis-quick-table" id="analysisQuickTable">
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>Avg Max</th>
                            <th>Avg Min</th>
                            <th>Peak</th>
                            <th>Lowest</th>
                            <th>Rainfall</th>
                            <th>Sunshine</th>
                            <th>Days</th>
                        </tr>
                    </thead>
                    <tbody id="analysisQuickTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="analysis-table-card statistics-section">
            <div class="table-card-header">
                <div class="header-left">
                    <i class="ri-file-chart-line"></i>
                    <div>
                        <h3>Reliability Analysis</h3>
                        <p id="analysisDateRange">-</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button type="button" class="fluent-btn fluent-btn-ghost" onclick="exportAnalysisCSV()">
                        <i class="ri-download-2-line"></i> Export
                    </button>
                    <button type="button" class="fluent-btn fluent-btn-ghost" onclick="printAnalysis()">
                        <i class="ri-printer-line"></i> Print
                    </button>
                </div>
            </div>
            <div class="table-card-body">
                <div class="analysis-table-wrapper">
                    <table class="analysis-stats-table" id="analysisStatsTable">
                        <thead>
                            <tr class="header-main">
                                <th rowspan="3" class="col-station">Station</th>
                                <th colspan="4" class="group-stats">Temperature Statistics</th>
                                <th colspan="4" class="group-50">50% Reliability</th>
                                <th colspan="4" class="group-98">98% Reliability</th>
                            </tr>
                            <tr class="header-sub">
                                <th colspan="2">High</th>
                                <th colspan="2">Low</th>
                                <th colspan="2">Max</th>
                                <th colspan="2">Min</th>
                                <th colspan="2">Max</th>
                                <th colspan="2">Min</th>
                            </tr>
                            <tr class="header-cols">
                                <th>Avg</th>
                                <th>Std</th>
                                <th>Avg</th>
                                <th>Std</th>
                                <th>Air</th>
                                <th>PVT</th>
                                <th>Air</th>
                                <th>PVT</th>
                                <th>Air</th>
                                <th>PVT</th>
                                <th>Air</th>
                                <th>PVT</th>
                            </tr>
                        </thead>
                        <tbody id="analysisStatsTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="table-legend">
                <div class="legend-item"><span class="legend-dot high"></span> High Temperature</div>
                <div class="legend-item"><span class="legend-dot low"></span> Low Temperature</div>
                <div class="legend-item"><span class="legend-dot pvt"></span> PVT (Pavement Temp)</div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="analysisEmptyState" class="analysis-empty-state">
        <div class="empty-illustration">
            <div class="empty-circle">
                <i class="ri-bar-chart-grouped-line"></i>
            </div>
        </div>
        <h3>Compare & Analyze Stations</h3>
        <p>Select up to 6 stations, set the date range, and click <strong>"Run Analysis"</strong></p>
        <div class="empty-features">
            <div class="feature-item">
                <i class="ri-bar-chart-line"></i>
                <span>Visual Comparison</span>
            </div>
            <div class="feature-item">
                <i class="ri-percent-line"></i>
                <span>Reliability Levels</span>
            </div>
            <div class="feature-item">
                <i class="ri-road-line"></i>
                <span>PVT Calculations</span>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <div class="modal-header-icon edit" id="editModalIcon"><i class="ri-pencil-line"></i></div>
            <div class="modal-header-content">
                <h2 class="modal-title" id="editModalTitle">Edit Weather Record</h2>
                <p class="modal-subtitle" id="editModalSubtitle">Update weather information</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('editModal')"><i class="ri-close-line"></i></button>
        </div>
        <form id="weatherForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label fluent-label-required" for="modal_station_id">
                                <i class="ri-map-pin-line"></i> Station
                            </label>
                            <select name="station_id" id="modal_station_id" class="fluent-select" required>
                                <option value="">Select Station</option>
                                @foreach($stations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label fluent-label-required" for="modal_record_date">
                                <i class="ri-calendar-line"></i> Record Date
                            </label>
                            <input type="date" class="fluent-input" name="record_date" id="modal_record_date" required>
                        </div>
                    </div>
                </div>
                <div class="form-section-title"><i class="ri-temp-hot-line"></i> Temperature</div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Max (°C)</label>
                            <input type="number" class="fluent-input" name="max_temp" id="modal_max_temp" step="0.01">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Min (°C)</label>
                            <input type="number" class="fluent-input" name="mini_temp" id="modal_mini_temp" step="0.01">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Avg (°C)</label>
                            <input type="number" class="fluent-input" name="avg_temp" id="modal_avg_temp" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="form-section-title"><i class="ri-water-percent-line"></i> Other Data</div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Humidity (%)</label>
                            <input type="number" class="fluent-input" name="humidity" id="modal_humidity" step="0.01">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Rainfall (mm)</label>
                            <input type="number" class="fluent-input" name="total_rain_fall" id="modal_total_rain_fall" step="0.01">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Sunshine (hrs)</label>
                            <input type="number" class="fluent-input" name="total_sunshine_hour" id="modal_total_sunshine_hour" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Dry Bulb (°C)</label>
                            <input type="number" class="fluent-input" name="dry_bulb" id="modal_dry_bulb" step="0.01">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">Dew Point (°C)</label>
                            <input type="number" class="fluent-input" name="dew_point" id="modal_dew_point" step="0.01">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="fluent-btn fluent-btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="fluent-btn fluent-btn-primary" id="submitBtn">
                    <span id="submitBtnText">Save</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


@section('styles')
<link href="/css/daily-weather.css" rel="stylesheet">
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let currentMode = 'stationAnalytics';
let currentPage = 1;
let currentDataView = 'cards';
let monthlyRecordsChart, yearlyTrendsChart;
let analysisTempCompareChart, analysisRainCompareChart; // For station analysis
const perPage = 12;

// Initialize
$(function() {
    initCharts();

    // Set default dates for Station Analysis (last year)
    const lastYear = new Date().getFullYear() - 1;
    $('#analysisFromDate').val(lastYear + '-01-01');
    $('#analysisToDate').val(lastYear + '-12-31');

    // Handle URL query parameters for direct navigation
    const urlParams = new URLSearchParams(window.location.search);
    const urlMode = urlParams.get('mode');
    const urlStation = urlParams.get('station');

    if (urlMode === 'stationAnalytics' && urlStation) {
        // Set the station dropdown value if station is provided
        if ($('#analyticsStation option[value="' + urlStation + '"]').length > 0) {
            $('#analyticsStation').val(urlStation);
        }
        // Switch to station analytics mode
        switchMode('stationAnalytics');
    } else if (urlMode && ['stationAnalytics', 'calendar', 'records', 'data', 'stationAnalysis'].includes(urlMode)) {
        switchMode(urlMode);
    } else {
        loadStationAnalytics();
    }

    // Station Analysis event handlers
    $('#runAnalysis').on('click', runStationAnalysis);

    // Limit station selection to 6
    $(document).on('change', '.analysis-station-cb', function() {
        const checkedCount = $('.analysis-station-cb:checked').length;
        if (checkedCount >= 6) {
            $('.analysis-station-cb:not(:checked)').prop('disabled', true);
        } else {
            $('.analysis-station-cb').prop('disabled', false);
        }
    });

    // Mode toggle
    $('.mode-btn').on('click', function() {
        const mode = $(this).data('mode');
        switchMode(mode);
    });

    // Filter toggle
    $('#toggle_filter').on('click', function() {
        $('#filter_body').slideToggle(250);
        $(this).toggleClass('expanded');
    });

    // Filter actions
    $('#filter_button').on('click', () => loadDataList(1));
    $('#reset_button').on('click', function() {
        $('#station_id, #from_date, #to_date').val('');
        loadDataList(1);
    });

    // View toggle (cards/table)
    $('.toggle-btn').on('click', function() {
        $('.toggle-btn').removeClass('active');
        $(this).addClass('active');
        currentDataView = $(this).data('view');
        toggleDataView();
    });

    // Calendar controls
    $('#calendarStation, #calendarYear, #calendarMetric').on('change', loadCalendar);

    // Export CSV
    $('#export_csv').on('click', exportCSV);
});

function switchMode(mode) {
    currentMode = mode;
    $('.mode-btn').removeClass('active');
    $(`.mode-btn[data-mode="${mode}"]`).addClass('active');
    $('.view-section').hide();
    $(`#${mode}View`).show();

    if (mode === 'stationAnalytics') loadStationAnalytics();
    else if (mode === 'calendar') loadCalendar();
    else if (mode === 'records') loadRecords();
    else if (mode === 'data') loadDataList(1);
    else if (mode === 'stationAnalysis') { /* Analysis is generated on button click */ }
}

function showLoading() { $('#loadingOverlay').addClass('active'); }
function hideLoading() { $('#loadingOverlay').removeClass('active'); }

// ==================== CALENDAR HEATMAP ====================
function loadCalendar() {
    showLoading();

    // Get current year value, default to current year if not set
    let yearVal = $('#calendarYear').val();
    if (!yearVal) {
        yearVal = new Date().getFullYear();
    }

    const params = {
        station_id: $('#calendarStation').val(),
        year: yearVal,
        metric: $('#calendarMetric').val() || 'max_temp'
    };

    $.get("{{ route('admin.daily-weathers.calendarData') }}", params, function(res) {
        hideLoading();

        // Populate years dropdown if not already populated
        if (res.available_years && $('#calendarYear option').length === 0) {
            res.available_years.forEach(y => {
                $('#calendarYear').append(`<option value="${y}" ${y == res.year ? 'selected' : ''}>${y}</option>`);
            });
        }

        // Use returned year or fallback to requested year
        const displayYear = res.year || yearVal;
        const displayMetric = res.metric || params.metric;
        const displayData = res.data || [];

        // Small delay to ensure DOM is ready
        setTimeout(function() {
            renderCalendarHeatmap(displayData, displayMetric, displayYear);
        }, 50);
    }).fail(function() {
        hideLoading();
        console.error('Failed to load calendar data');
    });
}

function renderCalendarHeatmap(data, metric, year) {
    // Ensure year is a number
    year = parseInt(year) || new Date().getFullYear();

    const dataMap = {};
    let minVal = Infinity, maxVal = -Infinity;

    if (data && data.length > 0) {
        data.forEach(d => {
            dataMap[d.date] = d;
            if (d.value !== null) {
                minVal = Math.min(minVal, d.value);
                maxVal = Math.max(maxVal, d.value);
            }
        });
    }

    // Legend
    const colors = metric === 'rainfall' ?
        ['#f0f9ff', '#bae6fd', '#7dd3fc', '#38bdf8', '#0284c7', '#0369a1'] :
        metric === 'humidity' ?
        ['#ecfdf5', '#a7f3d0', '#6ee7b7', '#34d399', '#10b981', '#047857'] :
        ['#dbeafe', '#93c5fd', '#60a5fa', '#f97316', '#dc2626', '#7f1d1d'];

    let legendHtml = '<span class="legend-label">Low</span><div class="legend-scale">';
    colors.forEach(c => legendHtml += `<div class="legend-block" style="background:${c}"></div>`);
    legendHtml += '</div><span class="legend-label">High</span>';
    $('#calendarLegend').html(legendHtml);

    // Calendar
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    let html = '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">';

    for (let m = 0; m < 12; m++) {
        html += `<div class="calendar-month"><div class="calendar-month-title">${months[m]}</div><div class="calendar-days">`;

        const firstDay = new Date(year, m, 1).getDay();
        const daysInMonth = new Date(year, m + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) {
            html += '<div class="calendar-day empty"></div>';
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${year}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const dayData = dataMap[dateStr];
            let bgColor = '#e5e7eb';

            if (dayData && dayData.value !== null) {
                const ratio = maxVal !== minVal ? (dayData.value - minVal) / (maxVal - minVal) : 0.5;
                const colorIndex = Math.min(Math.floor(ratio * colors.length), colors.length - 1);
                bgColor = colors[colorIndex];
            }

            html += `<div class="calendar-day" style="background:${bgColor}"
                data-date="${dateStr}"
                data-value="${dayData ? dayData.value : ''}"
                data-max="${dayData ? dayData.max_temp : ''}"
                data-min="${dayData ? dayData.min_temp : ''}"
                data-rain="${dayData ? dayData.rainfall : ''}"
                data-humidity="${dayData ? dayData.humidity : ''}"></div>`;
        }

        html += '</div></div>';
    }
    html += '</div>';
    $('#calendarHeatmap').html(html);

    // Tooltip
    $('.calendar-day:not(.empty)').on('mouseenter', function(e) {
        const $this = $(this);
        const date = $this.data('date');
        const tooltip = $('#calendarTooltip');
        tooltip.html(`
            <strong>${date}</strong><br>
            Max: ${$this.data('max') || '-'}°C<br>
            Min: ${$this.data('min') || '-'}°C<br>
            Rain: ${$this.data('rain') || '-'}mm<br>
            Humidity: ${$this.data('humidity') || '-'}%
        `).css({ top: e.pageY - 80, left: e.pageX + 10, display: 'block' });
    }).on('mouseleave', () => $('#calendarTooltip').hide());
}

// ==================== STATION ANALYSIS ====================
function selectAllAnalysisStations() {
    const maxStations = 6;
    let count = 0;
    $('.analysis-station-cb').each(function() {
        if (count < maxStations) {
            $(this).prop('checked', true).prop('disabled', false);
            count++;
        } else {
            $(this).prop('checked', false).prop('disabled', true);
        }
    });
}

function deselectAllAnalysisStations() {
    $('.analysis-station-cb').prop('checked', false).prop('disabled', false);
}

function initAnalysisCharts() {
    if (analysisTempCompareChart) return; // Already initialized

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: { x: { grid: { display: false } }, y: { grid: { color: 'rgba(0,0,0,0.05)' } } }
    };

    analysisTempCompareChart = new Chart($('#analysisTempChart'), {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: chartOptions
    });

    analysisRainCompareChart = new Chart($('#analysisRainChart'), {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: { ...chartOptions, plugins: { legend: { display: false } } }
    });
}

function runStationAnalysis() {
    const selectedStations = [];
    $('.analysis-station-cb:checked').each(function() {
        selectedStations.push($(this).val());
    });

    if (selectedStations.length === 0) {
        alert('Please select at least one station');
        return;
    }

    showLoading();
    initAnalysisCharts();

    const fromDate = $('#analysisFromDate').val();
    const toDate = $('#analysisToDate').val();

    // Build parameters
    const params = new URLSearchParams();
    selectedStations.forEach(id => params.append('stations[]', id));
    params.append('from_date', fromDate);
    params.append('to_date', toDate);
    params.append('max_count', $('#analysisMaxCount').val());
    params.append('min_count', $('#analysisMinCount').val());
    params.append('sd_type', $('#analysisSdType').val());
    params.append('period', 'custom');

    // Fetch both comparison and reliability data
    $.when(
        $.get("{{ route('admin.daily-weathers.comparisonData') }}?" + params.toString()),
        $.get("{{ route('admin.daily-weathers.weatherReportData') }}?" + params.toString())
    ).done(function(compRes, reportRes) {
        hideLoading();

        const comparisonData = compRes[0].data;
        const reportData = reportRes[0];

        if ((!comparisonData || comparisonData.length === 0) && (!reportData.data || reportData.data.length === 0)) {
            $('#analysisResults').hide();
            $('#analysisEmptyState').show();
            $('#analysisEmptyState h3').text('No Data Found');
            return;
        }

        // Update date range display
        $('#analysisDateRange').text(`Data from ${formatDate(fromDate)} to ${formatDate(toDate)}`);

        // Render summary
        renderAnalysisSummary(reportData.summary);

        // Render comparison charts
        if (comparisonData && comparisonData.length > 0) {
            renderAnalysisCharts(comparisonData);
            renderQuickComparisonTable(comparisonData);
        }

        // Render reliability statistics table
        if (reportData.data && reportData.data.length > 0) {
            renderAnalysisStatsTable(reportData.data);
        }

        $('#analysisResults').show();
        $('#analysisEmptyState').hide();

    }).fail(function() {
        hideLoading();
        alert('Failed to load analysis data. Please try again.');
    });
}

function renderAnalysisSummary(summary) {
    if (!summary) return;
    let html = `
        <div class="summary-stat-card stations">
            <div class="summary-stat-icon"><i class="ri-map-pin-line"></i></div>
            <div class="summary-stat-info">
                <h4>Stations Analyzed</h4>
                <div class="summary-stat-value">${summary.station_count || 0}</div>
            </div>
        </div>
        <div class="summary-stat-card hot">
            <div class="summary-stat-icon"><i class="ri-temp-hot-line"></i></div>
            <div class="summary-stat-info">
                <h4>98% Max Temperature</h4>
                <div class="summary-stat-value">${summary.overall_high_max || '-'}<span>°C</span></div>
            </div>
        </div>
        <div class="summary-stat-card cold">
            <div class="summary-stat-icon"><i class="ri-temp-cold-line"></i></div>
            <div class="summary-stat-info">
                <h4>98% Min Temperature</h4>
                <div class="summary-stat-value">${summary.overall_low_min || '-'}<span>°C</span></div>
            </div>
        </div>
        <div class="summary-stat-card range">
            <div class="summary-stat-icon"><i class="ri-arrow-left-right-line"></i></div>
            <div class="summary-stat-info">
                <h4>Temperature Range</h4>
                <div class="summary-stat-value">${summary.temp_range || '-'}<span>°C</span></div>
            </div>
        </div>
    `;
    $('#analysisSummary').html(html);
}

function renderAnalysisCharts(data) {
    const labels = data.map(d => d.station_name);
    const colors = ['#0078D4', '#D83B01', '#107C10', '#FFB900', '#8764B8', '#00B294'];

    // Temperature chart
    analysisTempCompareChart.data.labels = labels;
    analysisTempCompareChart.data.datasets = [
        { label: 'Avg Max', data: data.map(d => d.avg_max_temp), backgroundColor: 'rgba(216,59,1,0.7)' },
        { label: 'Avg Min', data: data.map(d => d.avg_min_temp), backgroundColor: 'rgba(0,120,212,0.7)' }
    ];
    analysisTempCompareChart.update();

    // Rainfall chart
    analysisRainCompareChart.data.labels = labels;
    analysisRainCompareChart.data.datasets = [{
        label: 'Rainfall (mm)',
        data: data.map(d => d.total_rainfall),
        backgroundColor: data.map((_, i) => colors[i % colors.length])
    }];
    analysisRainCompareChart.update();
}

function renderQuickComparisonTable(data) {
    let tbody = '';
    data.forEach(d => {
        tbody += `<tr>
            <td><strong>${escapeHtml(d.station_name)}</strong></td>
            <td>${d.avg_max_temp}°C</td>
            <td>${d.avg_min_temp}°C</td>
            <td style="color:#D83B01">${d.peak_temp}°C</td>
            <td style="color:#0078D4">${d.lowest_temp}°C</td>
            <td>${d.total_rainfall}mm</td>
            <td>${d.total_sunshine}h</td>
            <td>${d.days_recorded}</td>
        </tr>`;
    });
    $('#analysisQuickTableBody').html(tbody);
}

function renderAnalysisStatsTable(data) {
    let tbody = '';
    data.forEach(row => {
        tbody += `
            <tr>
                <td class="col-station">${escapeHtml(row.station_name)}</td>
                <td class="val-high">${row.high_avg}°C</td>
                <td>${row.high_std}</td>
                <td class="val-low">${row.low_avg}°C</td>
                <td>${row.low_std}</td>
                <td class="val-high">${row.max_air_50}°C</td>
                <td class="val-pvt">${row.pvt_max_50}°C</td>
                <td class="val-low">${row.min_air_50}°C</td>
                <td class="val-pvt">${row.pvt_min_50}°C</td>
                <td class="val-high">${row.max_air_98}°C</td>
                <td class="val-pvt">${row.pvt_max_98}°C</td>
                <td class="val-low">${row.min_air_98}°C</td>
                <td class="val-pvt">${row.pvt_min_98}°C</td>
            </tr>
        `;
    });
    $('#analysisStatsTableBody').html(tbody);
}

function exportAnalysisCSV() {
    const table = document.getElementById('analysisStatsTable');
    if (!table) return;

    let csv = [];

    // Headers
    const headerRows = table.querySelectorAll('thead tr');
    headerRows.forEach(row => {
        const cols = row.querySelectorAll('th');
        const rowData = [];
        cols.forEach(col => {
            let text = col.innerText.trim();
            if (col.colSpan > 1) {
                for (let i = 0; i < col.colSpan; i++) {
                    rowData.push('"' + text + '"');
                }
            } else {
                rowData.push('"' + text + '"');
            }
        });
        csv.push(rowData.join(','));
    });

    // Body rows
    const bodyRows = table.querySelectorAll('tbody tr');
    bodyRows.forEach(row => {
        const cols = row.querySelectorAll('td');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.innerText.trim().replace('°C', '') + '"');
        });
        csv.push(rowData.join(','));
    });

    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'station_analysis_' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}

function printAnalysis() {
    const printContent = document.getElementById('analysisResults').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Station Comparison & Analysis Report</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 20px; }
                .analysis-summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
                .summary-stat-card { padding: 16px; border: 1px solid #ddd; border-radius: 8px; }
                .summary-stat-info h4 { margin: 0 0 4px; font-size: 11px; color: #666; text-transform: uppercase; }
                .summary-stat-value { font-size: 24px; font-weight: 700; }
                .analysis-table-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
                .table-card-header { padding: 12px 16px; background: #f5f5f5; }
                .analysis-stats-table, .analysis-quick-table { width: 100%; border-collapse: collapse; }
                .analysis-stats-table th, .analysis-stats-table td,
                .analysis-quick-table th, .analysis-quick-table td { padding: 8px 10px; border: 1px solid #ddd; text-align: center; font-size: 11px; }
                .analysis-stats-table th, .analysis-quick-table th { background: #f5f5f5; }
                .analysis-stats-table .header-main th { background: #333; color: white; }
                .val-high { color: #D83B01; font-weight: 600; }
                .val-low { color: #0078D4; font-weight: 600; }
                .val-pvt { color: #8764B8; }
                .table-legend, .header-actions, .summary-stat-icon, .analysis-charts-grid { display: none; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <h1 style="text-align: center; margin-bottom: 24px;">Station Comparison & Analysis Report</h1>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// ==================== STATION ANALYTICS ====================
let analyticsTempChart, analyticsRainChart, analyticsSunshineChart, analyticsHumidityChart;
let analyticsViewType = 'year';

// Initialize charts when document ready
function initAnalyticsCharts() {
    if (analyticsTempChart) return; // Already initialized

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } }
    };

    analyticsTempChart = new Chart($('#analyticsTempChart'), {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: chartOptions
    });

    analyticsRainChart = new Chart($('#analyticsRainChart'), {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: chartOptions
    });

    analyticsSunshineChart = new Chart($('#analyticsSunshineChart'), {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: { ...chartOptions, elements: { line: { fill: true } } }
    });

    analyticsHumidityChart = new Chart($('#analyticsHumidityChart'), {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: { ...chartOptions, elements: { line: { fill: true } } }
    });
}

// Handle view toggle
$(document).on('click', '#analyticsViewToggle .view-btn', function() {
    $('#analyticsViewToggle .view-btn').removeClass('active');
    $(this).addClass('active');
    analyticsViewType = $(this).data('view');

    // Show/hide appropriate controls based on view type
    if (analyticsViewType === 'month') {
        $('#analyticsYear').show();
        $('#analyticsMonth').show();
        $('#decadeRangeControls').hide();
    } else if (analyticsViewType === 'year') {
        $('#analyticsYear').show();
        $('#analyticsMonth').hide();
        $('#decadeRangeControls').hide();
    } else if (analyticsViewType === 'decade') {
        $('#analyticsYear').hide();
        $('#analyticsMonth').hide();
        $('#decadeRangeControls').show();
    }

    loadStationAnalytics();
});

// Event handlers for analytics controls
$('#analyticsStation, #analyticsYear, #analyticsMonth').on('change', function() {
    if (currentMode === 'stationAnalytics') {
        loadStationAnalytics();
    }
});

function loadStationAnalytics() {
    initAnalyticsCharts();
    showLoading();

    const stationId = $('#analyticsStation').val();
    const year = $('#analyticsYear').val();
    const month = $('#analyticsMonth').val();
    const decadeStart = $('#decadeStart').val();
    const decadeEnd = $('#decadeEnd').val();

    let params = {
        view: analyticsViewType,
        year: year,
        month: month
    };

    // For decade view, pass decade range
    if (analyticsViewType === 'decade') {
        params.decade_start = parseInt(decadeStart);
        params.decade_end = parseInt(decadeEnd) + 9; // Include full decade (e.g., 2020-2029)
    } else {
        params.decade_start = Math.floor(year / 10) * 10;
    }

    $.get(`/admin/stations/${stationId}/analytics-data`, params, function(res) {
        hideLoading();
        renderAnalyticsStats(res.summary);
        renderAnalyticsCharts(res.data);
        renderAnalyticsTable(res.data);
    }).fail(function() {
        hideLoading();
        console.error('Failed to load analytics data');
    });
}

function renderAnalyticsStats(summary) {
    if (!summary) summary = {};
    let html = '';

    if (analyticsViewType === 'month') {
        html = `
            <div class="stat-card">
                <div class="stat-icon temp"><i class="ri-temp-hot-line"></i></div>
                <div class="stat-info"><h4>Avg Max Temp</h4><div class="stat-value">${summary.avg_max_temp || 0}<span>°C</span></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon temp" style="background: linear-gradient(135deg, #0078D4, #00BCF2);"><i class="ri-temp-cold-line"></i></div>
                <div class="stat-info"><h4>Avg Min Temp</h4><div class="stat-value">${summary.avg_min_temp || 0}<span>°C</span></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rain"><i class="ri-rainy-line"></i></div>
                <div class="stat-info"><h4>Total Rainfall</h4><div class="stat-value">${summary.total_rainfall || 0}<span>mm</span></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sun"><i class="ri-sun-line"></i></div>
                <div class="stat-info"><h4>Total Sunshine</h4><div class="stat-value">${summary.total_sunshine || 0}<span>hrs</span></div></div>
            </div>
        `;
    } else {
        html = `
            <div class="stat-card">
                <div class="stat-icon temp"><i class="ri-temp-hot-line"></i></div>
                <div class="stat-info"><h4>Avg Max Temp</h4><div class="stat-value">${summary.avg_max_temp || 0}<span>°C</span></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rain"><i class="ri-rainy-line"></i></div>
                <div class="stat-info"><h4>Total Rainfall</h4><div class="stat-value">${summary.total_rainfall || 0}<span>mm</span></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon humidity"><i class="ri-drop-line"></i></div>
                <div class="stat-info"><h4>Rainy Days</h4><div class="stat-value">${summary.rainy_days || 0}<span>days</span></div></div>
            </div>
        `;
    }
    $('#analyticsStats').html(html);
}

function renderAnalyticsCharts(data) {
    if (!data || !data.length) return;

    let labels, maxTemps, minTemps, rainfall, sunshine, humidity;

    if (analyticsViewType === 'month') {
        labels = data.map(d => d.date);
        maxTemps = data.map(d => d.max_temp);
        minTemps = data.map(d => d.min_temp);
        rainfall = data.map(d => d.rainfall);
        sunshine = data.map(d => d.sunshine);
        humidity = data.map(d => d.humidity);
    } else {
        labels = data.map(d => d.month || d.year);
        maxTemps = data.map(d => d.avg_max_temp);
        minTemps = data.map(d => d.avg_min_temp);
        rainfall = data.map(d => d.total_rainfall);
        sunshine = data.map(d => d.total_sunshine);
        humidity = data.map(d => d.avg_humidity);
    }

    // Temperature Chart
    analyticsTempChart.data.labels = labels;
    analyticsTempChart.data.datasets = [
        { label: 'Max Temp (°C)', data: maxTemps, borderColor: '#D83B01', backgroundColor: 'rgba(216,59,1,0.1)', tension: 0.4 },
        { label: 'Min Temp (°C)', data: minTemps, borderColor: '#0078D4', backgroundColor: 'rgba(0,120,212,0.1)', tension: 0.4 }
    ];
    analyticsTempChart.update();

    // Rainfall Chart
    analyticsRainChart.data.labels = labels;
    analyticsRainChart.data.datasets = [{
        label: 'Rainfall (mm)', data: rainfall,
        backgroundColor: 'rgba(0,120,212,0.7)', borderColor: '#0078D4', borderWidth: 1
    }];
    analyticsRainChart.update();

    // Sunshine Chart
    analyticsSunshineChart.data.labels = labels;
    analyticsSunshineChart.data.datasets = [{
        label: 'Sunshine (hrs)', data: sunshine,
        borderColor: '#FFB900', backgroundColor: 'rgba(255,185,0,0.2)', fill: true, tension: 0.4
    }];
    analyticsSunshineChart.update();

    // Humidity Chart
    analyticsHumidityChart.data.labels = labels;
    analyticsHumidityChart.data.datasets = [{
        label: 'Humidity (%)', data: humidity,
        borderColor: '#00B294', backgroundColor: 'rgba(0,178,148,0.2)', fill: true, tension: 0.4
    }];
    analyticsHumidityChart.update();
}

function renderAnalyticsTable(data) {
    if (!data || !data.length) {
        $('#analyticsTableHead').html('<tr><th colspan="6">No data available</th></tr>');
        $('#analyticsTableBody').html('');
        return;
    }

    let thead = '', tbody = '';

    if (analyticsViewType === 'month') {
        thead = '<tr><th>Date</th><th>Max Temp</th><th>Min Temp</th><th>Rainfall</th><th>Humidity</th><th>Sunshine</th></tr>';
        data.forEach(d => {
            tbody += `<tr>
                <td>${d.full_date || d.date}</td>
                <td>${d.max_temp}°C</td>
                <td>${d.min_temp}°C</td>
                <td>${d.rainfall}mm</td>
                <td>${d.humidity}%</td>
                <td>${d.sunshine}hrs</td>
            </tr>`;
        });
    } else {
        thead = '<tr><th>Period</th><th>Avg Max</th><th>Avg Min</th><th>Rainfall</th><th>Rainy Days</th><th>Sunshine</th></tr>';
        data.forEach(d => {
            tbody += `<tr>
                <td>${d.month || d.year}</td>
                <td>${d.avg_max_temp}°C</td>
                <td>${d.avg_min_temp}°C</td>
                <td>${d.total_rainfall}mm</td>
                <td>${d.rainy_days || 0}</td>
                <td>${d.total_sunshine || 0}hrs</td>
            </tr>`;
        });
    }

    $('#analyticsTableHead').html(thead);
    $('#analyticsTableBody').html(tbody);
}

// ==================== RECORDS ====================
function loadRecords() {
    showLoading();
    $.get("{{ route('admin.daily-weathers.recordsData') }}", { station_id: $('#recordsStation').val() }, function(res) {
        hideLoading();
        renderRecords(res);
    });
}

function renderRecords(data) {
    const e = data.extremes;
    let html = '';

    if (e.hottest) {
        html += `<div class="extreme-card hot">
            <div class="extreme-icon"><i class="ri-fire-line"></i></div>
            <div class="extreme-title">Hottest Day</div>
            <div class="extreme-value">${e.hottest.value}°C</div>
            <div class="extreme-meta">${e.hottest.station}<br>${e.hottest.date}</div>
        </div>`;
    }
    if (e.coldest) {
        html += `<div class="extreme-card cold">
            <div class="extreme-icon"><i class="ri-snowy-line"></i></div>
            <div class="extreme-title">Coldest Day</div>
            <div class="extreme-value">${e.coldest.value}°C</div>
            <div class="extreme-meta">${e.coldest.station}<br>${e.coldest.date}</div>
        </div>`;
    }
    if (e.wettest) {
        html += `<div class="extreme-card wet">
            <div class="extreme-icon"><i class="ri-rainy-line"></i></div>
            <div class="extreme-title">Wettest Day</div>
            <div class="extreme-value">${e.wettest.value}mm</div>
            <div class="extreme-meta">${e.wettest.station}<br>${e.wettest.date}</div>
        </div>`;
    }
    if (e.sunniest) {
        html += `<div class="extreme-card sun">
            <div class="extreme-icon"><i class="ri-sun-line"></i></div>
            <div class="extreme-title">Sunniest Day</div>
            <div class="extreme-value">${e.sunniest.value}h</div>
            <div class="extreme-meta">${e.sunniest.station}<br>${e.sunniest.date}</div>
        </div>`;
    }
    if (e.humidest) {
        html += `<div class="extreme-card humid">
            <div class="extreme-icon"><i class="ri-drop-line"></i></div>
            <div class="extreme-title">Most Humid</div>
            <div class="extreme-value">${e.humidest.value}%</div>
            <div class="extreme-meta">${e.humidest.station}<br>${e.humidest.date}</div>
        </div>`;
    }
    $('#extremeRecords').html(html);

    // Monthly chart
    const months = data.monthly_records.map(m => m.month);
    monthlyRecordsChart.data.labels = months;
    monthlyRecordsChart.data.datasets = [
        { label: 'Max Record', data: data.monthly_records.map(m => m.max_temp_record), borderColor: '#D83B01', backgroundColor: 'rgba(216,59,1,0.1)', fill: true, tension: 0.4 },
        { label: 'Avg Max', data: data.monthly_records.map(m => m.avg_max_temp), borderColor: '#f97316', borderDash: [5,5], fill: false, tension: 0.4 },
        { label: 'Avg Min', data: data.monthly_records.map(m => m.avg_min_temp), borderColor: '#0078D4', borderDash: [5,5], fill: false, tension: 0.4 },
        { label: 'Min Record', data: data.monthly_records.map(m => m.min_temp_record), borderColor: '#0078D4', backgroundColor: 'rgba(0,120,212,0.1)', fill: true, tension: 0.4 }
    ];
    monthlyRecordsChart.update();

    // Yearly chart
    yearlyTrendsChart.data.labels = data.yearly_trends.map(y => y.year);
    yearlyTrendsChart.data.datasets = [
        { label: 'Avg Max Temp', data: data.yearly_trends.map(y => y.avg_max_temp), borderColor: '#D83B01', tension: 0.4, fill: false },
        { label: 'Avg Min Temp', data: data.yearly_trends.map(y => y.avg_min_temp), borderColor: '#0078D4', tension: 0.4, fill: false }
    ];
    yearlyTrendsChart.update();
}

// ==================== DATA LIST ====================
function loadDataList(page = 1) {
    currentPage = page;
    $('#loading_state').show();
    $('#weather_cards, #weather_table_container, #empty_state').hide();

    $.get("{{ route('admin.daily-weathers.index') }}", {
        page, per_page: perPage,
        station_id: $('#station_id').val(),
        from_date: $('#from_date').val(),
        to_date: $('#to_date').val()
    }, function(res) {
        $('#loading_state').hide();
        if (res.data && res.data.length > 0) {
            renderDataList(res.data);
            renderPagination(res);
            $('#results_count').html(`Showing <strong>${res.from}-${res.to}</strong> of <strong>${res.total}</strong>`);
            toggleDataView();
        } else {
            $('#empty_state').show();
            $('#results_count').text('No records found');
        }
    });
}

function renderDataList(data) {
    let cardsHtml = '', tableHtml = '';
    data.forEach(w => {
        const station = w.station ? w.station.station_name : 'Unknown';
        cardsHtml += `
            <div class="weather-card">
                <div class="card-actions">
                    @can('daily_weather_edit')
                    <button class="card-action-icon edit" onclick="event.stopPropagation(); openEditModal(${w.id})"><i class="ri-pencil-line"></i></button>
                    @endcan
                    @can('daily_weather_delete')
                    <button class="card-action-icon delete" onclick="event.stopPropagation(); deleteWeather(${w.id})"><i class="ri-delete-bin-line"></i></button>
                    @endcan
                </div>
                <div class="weather-card-header">
                    <div class="weather-card-icon"><i class="ri-map-pin-line"></i></div>
                    <div>
                        <div class="weather-card-title">${escapeHtml(station)}</div>
                        <div class="weather-card-date"><i class="ri-calendar-line"></i> ${w.record_date || 'N/A'}</div>
                    </div>
                </div>
                <div class="weather-card-body">
                    <div class="temp-display">
                        <div class="temp-item"><div class="temp-label">Max</div><div class="temp-value high">${w.max_temp || '-'}°C</div></div>
                        <div class="temp-item"><div class="temp-label">Min</div><div class="temp-value low">${w.mini_temp || '-'}°C</div></div>
                    </div>
                    <div class="weather-stats-grid">
                        <div class="weather-stat"><div class="weather-stat-icon humidity"><i class="ri-drop-line"></i></div><div><div class="weather-stat-label">Humidity</div><div class="weather-stat-value">${w.humidity || '-'}%</div></div></div>
                        <div class="weather-stat"><div class="weather-stat-icon rain"><i class="ri-rainy-line"></i></div><div><div class="weather-stat-label">Rainfall</div><div class="weather-stat-value">${w.total_rain_fall || '-'}mm</div></div></div>
                        <div class="weather-stat"><div class="weather-stat-icon sun"><i class="ri-sun-line"></i></div><div><div class="weather-stat-label">Sunshine</div><div class="weather-stat-value">${w.total_sunshine_hour || '-'}h</div></div></div>
                        <div class="weather-stat"><div class="weather-stat-icon dew"><i class="ri-contrast-drop-line"></i></div><div><div class="weather-stat-label">Dew Point</div><div class="weather-stat-value">${w.dew_point || '-'}°C</div></div></div>
                    </div>
                </div>
            </div>`;

        tableHtml += `<tr>
            <td>${w.record_date || 'N/A'}</td>
            <td>${escapeHtml(station)}</td>
            <td>${w.max_temp || '-'}°C</td>
            <td>${w.mini_temp || '-'}°C</td>
            <td>${w.humidity || '-'}%</td>
            <td>${w.total_rain_fall || '-'}mm</td>
            <td>${w.total_sunshine_hour || '-'}h</td>
            <td>
                @can('daily_weather_edit')<button class="card-action-icon edit" onclick="openEditModal(${w.id})"><i class="ri-pencil-line"></i></button>@endcan
                @can('daily_weather_delete')<button class="card-action-icon delete" onclick="deleteWeather(${w.id})"><i class="ri-delete-bin-line"></i></button>@endcan
            </td>
        </tr>`;
    });
    $('#weather_cards').html(cardsHtml);
    $('#weather_table_body').html(tableHtml);
}

function toggleDataView() {
    if (currentDataView === 'cards') {
        $('#weather_cards').show();
        $('#weather_table_container').hide();
    } else {
        $('#weather_cards').hide();
        $('#weather_table_container').show();
    }
}

function renderPagination(res) {
    const { current_page, last_page } = res;
    let html = '<div class="pagination-container">';
    html += `<button class="page-btn" onclick="goToPage(${current_page-1})" ${current_page===1?'disabled':''}><i class="ri-arrow-left-s-line"></i></button>`;

    const start = Math.max(1, current_page - 2), end = Math.min(last_page, current_page + 2);
    if (start > 1) { html += '<button class="page-btn" onclick="goToPage(1)">1</button>'; if (start > 2) html += '<span>...</span>'; }
    for (let i = start; i <= end; i++) html += `<button class="page-btn ${i===current_page?'active':''}" onclick="goToPage(${i})">${i}</button>`;
    if (end < last_page) { if (end < last_page - 1) html += '<span>...</span>'; html += `<button class="page-btn" onclick="goToPage(${last_page})">${last_page}</button>`; }

    html += `<button class="page-btn" onclick="goToPage(${current_page+1})" ${current_page===last_page?'disabled':''}><i class="ri-arrow-right-s-line"></i></button></div>`;
    $('#pagination_top, #pagination_bottom').html(html);
}

function goToPage(page) { loadDataList(page); window.scrollTo({ top: 0, behavior: 'smooth' }); }

// ==================== UTILITIES ====================
function initCharts() {
    const defaultOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'top' } },
        scales: { x: { grid: { display: false } }, y: { grid: { color: 'rgba(0,0,0,0.05)' } } }
    };

    monthlyRecordsChart = new Chart($('#monthlyRecordsChart'), { type: 'line', data: { labels: [], datasets: [] }, options: defaultOpts });
    yearlyTrendsChart = new Chart($('#yearlyTrendsChart'), { type: 'line', data: { labels: [], datasets: [] }, options: defaultOpts });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function exportCSV() {
    const params = new URLSearchParams({
        station_id: $('#station_id').val(),
        from_date: $('#from_date').val(),
        to_date: $('#to_date').val(),
        export: 'csv'
    });
    window.location.href = "{{ route('admin.daily-weathers.index') }}?" + params.toString();
}

// Modal functions
function openModal(id) { document.getElementById(id).classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('active'); document.body.style.overflow = ''; }
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); }));

function openCreateModal() {
    document.getElementById('editModalIcon').className = 'modal-header-icon create';
    document.getElementById('editModalIcon').innerHTML = '<i class="ri-add-line"></i>';
    document.getElementById('editModalTitle').textContent = 'Add Weather Record';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('weatherForm').action = '{{ route("admin.daily-weathers.store") }}';
    document.getElementById('weatherForm').reset();
    openModal('editModal');
}

function openEditModal(id) {
    document.getElementById('editModalIcon').className = 'modal-header-icon edit';
    document.getElementById('editModalIcon').innerHTML = '<i class="ri-pencil-line"></i>';
    document.getElementById('editModalTitle').textContent = 'Edit Weather Record';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('weatherForm').action = `/admin/daily-weathers/${id}`;
    openModal('editModal');

    fetch(`/admin/daily-weathers/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const w = data.weather;
            $('#modal_station_id').val(w.station_id);
            $('#modal_record_date').val(w.record_date_raw);
            $('#modal_max_temp').val(w.max_temp);
            $('#modal_mini_temp').val(w.mini_temp);
            $('#modal_avg_temp').val(w.avg_temp);
            $('#modal_humidity').val(w.humidity);
            $('#modal_dry_bulb').val(w.dry_bulb);
            $('#modal_dew_point').val(w.dew_point);
            $('#modal_total_rain_fall').val(w.total_rain_fall);
            $('#modal_total_sunshine_hour').val(w.total_sunshine_hour);
        });
}

function deleteWeather(id) {
    if (!confirm('Delete this record?')) return;
    fetch(`/admin/daily-weathers/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(() => loadDataList(currentPage));
}

document.getElementById('weatherForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    document.getElementById('submitBtnText').textContent = 'Saving...';

    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('editModal');
            if (currentMode === 'data') loadDataList(currentPage);
            else if (currentMode === 'stationAnalytics') loadStationAnalytics();
        }
    })
    .finally(() => {
        btn.disabled = false;
        document.getElementById('submitBtnText').textContent = 'Save';
    });
});

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>
@endsection
