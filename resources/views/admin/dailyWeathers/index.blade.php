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
            <button type="button" class="mode-btn" data-mode="pavementAnalysis" title="Pavement Temperature Analysis">
                <i class="ri-route-line"></i>
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

<!-- ==================== PAVEMENT TEMPERATURE ANALYSIS VIEW ==================== -->
<div id="pavementAnalysisView" class="view-section" style="display: none;">
    <!-- Filter Card -->
    <div class="analysis-filter-card">
        <div class="analysis-filter-header">
            <div class="analysis-filter-title">
                <div class="analysis-title-icon">
                    <i class="ri-route-line"></i>
                </div>
                <div>
                    <h2>Pavement Temperature Analysis</h2>
                    <p>Calculate pavement temperatures using SUPERPAVE methodology (SHRP-A-648A)</p>
                </div>
            </div>
            <button type="button" id="runPavementAnalysis" class="fluent-btn fluent-btn-light fluent-btn-lg">
                <i class="ri-play-circle-line"></i> Run Analysis
            </button>
        </div>

        <div class="analysis-filter-body">
            <!-- Station Selection -->
            <div class="filter-section">
                <div class="filter-section-header">
                    <h4><i class="ri-map-pin-line"></i> Select Stations</h4>
                    <div class="station-quick-actions">
                        <button type="button" class="quick-action-btn" onclick="selectAllPavementStations()">
                            <i class="ri-checkbox-multiple-line"></i> Select All
                        </button>
                        <button type="button" class="quick-action-btn" onclick="deselectAllPavementStations()">
                            <i class="ri-checkbox-blank-line"></i> Deselect All
                        </button>
                    </div>
                </div>
                <div class="station-chips-grid" id="pavementStationGrid">
                    @foreach($stations as $id => $name)
                    <label class="station-chip">
                        <input type="checkbox" name="pavement_stations[]" value="{{ $id }}" class="pavement-station-cb">
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
                        <input type="date" id="pavementFromDate" class="fluent-input" value="2020-01-01">
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon param-date"><i class="ri-calendar-check-line"></i></div>
                    <div class="param-content">
                        <label>To Date</label>
                        <input type="date" id="pavementToDate" class="fluent-input" value="2025-12-31">
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon high"><i class="ri-temp-hot-line"></i></div>
                    <div class="param-content">
                        <label>Hot Days Count</label>
                        <input type="number" id="pavementHotDays" class="fluent-input" value="7" min="1" max="365">
                        <small class="param-hint">Number of hottest days to average</small>
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon low"><i class="ri-temp-cold-line"></i></div>
                    <div class="param-content">
                        <label>Cold Days Count</label>
                        <input type="number" id="pavementColdDays" class="fluent-input" value="1" min="1" max="365">
                        <small class="param-hint">Number of coldest days to average</small>
                    </div>
                </div>
                <div class="param-card">
                    <div class="param-icon calc"><i class="ri-calculator-line"></i></div>
                    <div class="param-content">
                        <label>SD Type</label>
                        <select id="pavementSdType" class="fluent-select">
                            <option value="population">Population SD</option>
                            <option value="sample">Sample SD</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Results -->
    <div id="pavementResults" style="display: none;">
        <!-- Summary Cards -->
        <div class="analysis-summary-grid" id="pavementSummary"></div>

        <!-- Main Results Table -->
        <div class="analysis-table-card statistics-section">
            <div class="table-card-header">
                <div class="header-left">
                    <i class="ri-route-line"></i>
                    <div>
                        <h3>Pavement Temperature Analysis Results</h3>
                        <p id="pavementDateRange">-</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button type="button" class="fluent-btn fluent-btn-ghost" onclick="exportPavementCSV()">
                        <i class="ri-download-2-line"></i> Export
                    </button>
                    <button type="button" class="fluent-btn fluent-btn-ghost" onclick="printPavementAnalysis()">
                        <i class="ri-printer-line"></i> Print
                    </button>
                </div>
            </div>
            <div class="table-card-body">
                <div class="analysis-table-wrapper">
                    <table class="analysis-stats-table pavement-table" id="pavementStatsTable">
                        <thead>
                            <tr class="header-main">
                                <th rowspan="3" class="col-station">Station</th>
                                <th rowspan="3">Long</th>
                                <th rowspan="3">Lat</th>
                                <th rowspan="3">Elev (m)</th>
                                <th colspan="4" class="group-stats">Air Temperature Statistics</th>
                                <th colspan="4" class="group-50">50% Reliability</th>
                                <th colspan="4" class="group-98">98% Reliability</th>
                            </tr>
                            <tr class="header-sub">
                                <th colspan="2">Low Temp</th>
                                <th colspan="2">High Temp</th>
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
                        <tbody id="pavementStatsTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="table-legend">
                <div class="legend-item"><span class="legend-dot high"></span> High Temperature</div>
                <div class="legend-item"><span class="legend-dot low"></span> Low Temperature</div>
                <div class="legend-item"><span class="legend-dot pvt"></span> PVT (Pavement Temp at 20mm)</div>
            </div>
            <div class="formula-note">
                <strong>Formula:</strong> T<sub>20mm</sub> = (T<sub>air</sub> - 0.00618 × lat² + 0.2289 × lat + 42.2) × 0.9545 - 17.78
                <span class="formula-source">Source: SHRP-A-648A (SUPERPAVE)</span>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="pavementEmptyState" class="analysis-empty-state">
        <div class="empty-illustration">
            <div class="empty-circle">
                <i class="ri-route-line"></i>
            </div>
        </div>
        <h3>Pavement Temperature Analysis</h3>
        <p>Select stations, set the date range, configure parameters and click <strong>"Run Analysis"</strong></p>
        <div class="empty-features">
            <div class="feature-item">
                <i class="ri-temp-hot-line"></i>
                <span>Hot Days Analysis</span>
            </div>
            <div class="feature-item">
                <i class="ri-temp-cold-line"></i>
                <span>Cold Days Analysis</span>
            </div>
            <div class="feature-item">
                <i class="ri-road-line"></i>
                <span>SUPERPAVE PVT</span>
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

<!-- Pavement Analysis Detail Modal -->
<div class="modal-overlay" id="pavementDetailModal">
    <div class="modal-container modal-xl">
        <div class="modal-header">
            <div class="modal-header-icon" style="background: linear-gradient(135deg, #107C10 0%, #0B5C0B 100%);">
                <i class="ri-route-line"></i>
            </div>
            <div class="modal-header-content">
                <h2 class="modal-title" id="pavementDetailTitle">Station Analysis</h2>
                <p class="modal-subtitle" id="pavementDetailSubtitle">SUPERPAVE Temperature Analysis Details</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('pavementDetailModal')"><i class="ri-close-line"></i></button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <!-- Tabs -->
            <div class="pavement-modal-tabs">
                <button type="button" class="pavement-tab-btn active" data-tab="graph">
                    <i class="ri-line-chart-line"></i> Standard Deviation Graph
                </button>
                <button type="button" class="pavement-tab-btn" data-tab="formula">
                    <i class="ri-calculator-line"></i> Formula & Calculation
                </button>
            </div>

            <!-- Tab Content: Graph -->
            <div class="pavement-tab-content active" id="pavementTabGraph">
                <!-- Bell Curve Distribution Charts -->
                <div class="bell-curve-grid">
                    <!-- High Temperature Bell Curve -->
                    <div class="bell-curve-card high">
                        <div class="bell-curve-header">
                            <div class="bell-header-left">
                                <h4><i class="ri-temp-hot-line"></i> High Temperature Distribution</h4>
                                <span class="bell-curve-subtitle" id="highTempSubtitle">7-day average per year</span>
                            </div>
                            <button class="formula-btn" onclick="showBellAnalytics('highTempBellCurve', 'formula')" title="View Formula & Methodology">
                                <i class="ri-formula"></i> Formula
                            </button>
                        </div>
                        <div class="bell-curve-chart-area">
                            <div class="bell-curve-wrapper">
                                <canvas id="highTempBellCurve"></canvas>
                            </div>
                            <!-- Overlay annotations -->
                            <div class="bell-curve-annotations" id="highTempAnnotations"></div>
                        </div>
                        <div class="bell-curve-data-table" id="highTempDataTable"></div>
                        <div class="bell-curve-stats" id="highTempStats"></div>
                    </div>

                    <!-- Low Temperature Bell Curve -->
                    <div class="bell-curve-card low">
                        <div class="bell-curve-header">
                            <div class="bell-header-left">
                                <h4><i class="ri-temp-cold-line"></i> Low Temperature Distribution</h4>
                                <span class="bell-curve-subtitle" id="lowTempSubtitle">Coldest day per year</span>
                            </div>
                            <button class="formula-btn" onclick="showBellAnalytics('lowTempBellCurve', 'formula')" title="View Formula & Methodology">
                                <i class="ri-formula"></i> Formula
                            </button>
                        </div>
                        <div class="bell-curve-chart-area">
                            <div class="bell-curve-wrapper">
                                <canvas id="lowTempBellCurve"></canvas>
                            </div>
                            <!-- Overlay annotations -->
                            <div class="bell-curve-annotations" id="lowTempAnnotations"></div>
                        </div>
                        <div class="bell-curve-data-table" id="lowTempDataTable"></div>
                        <div class="bell-curve-stats" id="lowTempStats"></div>
                    </div>
                </div>

                <!-- Yearly Data Points Table -->
                <div class="yearly-data-section" id="yearlyDataSection"></div>
            </div>

            <!-- Tab Content: Formula -->
            <div class="pavement-tab-content" id="pavementTabFormula">
                <div class="formula-explanation" id="pavementFormulaExplanation"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="fluent-btn fluent-btn-secondary" onclick="closeModal('pavementDetailModal')">Close</button>
        </div>
    </div>
</div>
@endsection


@section('styles')
<!-- Daily Weather Modular CSS -->
<link href="/css/daily-weather/variables.css" rel="stylesheet">
<link href="/css/daily-weather/base.css" rel="stylesheet">
<link href="/css/daily-weather/analytics.css" rel="stylesheet">
<link href="/css/daily-weather/data.css" rel="stylesheet">
<link href="/css/daily-weather/station-analysis.css" rel="stylesheet">
<link href="/css/daily-weather/pavement.css" rel="stylesheet">
<link href="/css/daily-weather/bell-curve.css" rel="stylesheet">
<link href="/css/daily-weather/responsive.css" rel="stylesheet">
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
    const urlMode = urlParams.get('mode') || urlParams.get('view'); // Support both 'mode' and 'view' params
    const urlStation = urlParams.get('station');

    if (urlMode === 'stationAnalytics' && urlStation) {
        // Set the station dropdown value if station is provided
        if ($('#analyticsStation option[value="' + urlStation + '"]').length > 0) {
            $('#analyticsStation').val(urlStation);
        }
        // Switch to station analytics mode
        switchMode('stationAnalytics');
    } else if (urlMode && ['stationAnalytics', 'calendar', 'records', 'data', 'stationAnalysis', 'pavementAnalysis'].includes(urlMode)) {
        switchMode(urlMode);
    } else {
        loadStationAnalytics();
    }

    // Station Analysis event handlers
    $('#runAnalysis').on('click', runStationAnalysis);
    $('#runPavementAnalysis').on('click', runPavementAnalysis);

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
    else if (mode === 'pavementAnalysis') { loadPavementAnalysisDefault(); }
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

// ==================== PAVEMENT TEMPERATURE ANALYSIS ====================
let pavementDataLoaded = false;
let pavementAnalysisData = []; // Store data globally for modal
const PAVEMENT_CACHE_KEY = 'pavementAnalysisCache';
const PAVEMENT_CACHE_EXPIRY = 30 * 60 * 1000; // 30 minutes

function selectAllPavementStations() {
    $('.pavement-station-cb').prop('checked', true);
}

function deselectAllPavementStations() {
    $('.pavement-station-cb').prop('checked', false);
}

// Cache management functions
function getPavementCache() {
    try {
        const cached = localStorage.getItem(PAVEMENT_CACHE_KEY);
        if (!cached) return null;
        const parsed = JSON.parse(cached);
        // Check if cache is expired
        if (Date.now() - parsed.timestamp > PAVEMENT_CACHE_EXPIRY) {
            localStorage.removeItem(PAVEMENT_CACHE_KEY);
            return null;
        }
        return parsed.data;
    } catch (e) {
        return null;
    }
}

function setPavementCache(data) {
    try {
        localStorage.setItem(PAVEMENT_CACHE_KEY, JSON.stringify({
            timestamp: Date.now(),
            data: data
        }));
    } catch (e) {
        // Storage might be full, ignore
    }
}

function clearPavementCache() {
    localStorage.removeItem(PAVEMENT_CACHE_KEY);
}

function loadPavementAnalysisDefault() {
    // Only auto-load once
    if (pavementDataLoaded) return;
    pavementDataLoaded = true;

    // Select all stations by default
    selectAllPavementStations();

    // Check cache first for faster loading
    const cached = getPavementCache();
    if (cached && cached.data && cached.data.length > 0) {
        // Use cached data
        renderPavementFromData(cached);
        return;
    }

    // Run analysis with default date range (2020-2025)
    showLoading();

    const params = new URLSearchParams();
    params.append('from_date', '2020-01-01');
    params.append('to_date', '2025-12-31');
    params.append('hot_days', $('#pavementHotDays').val() || 7);
    params.append('cold_days', $('#pavementColdDays').val() || 1);
    params.append('sd_type', $('#pavementSdType').val() || 'population');

    $.get("{{ route('admin.daily-weathers.pavementAnalysisData') }}?" + params.toString())
        .done(function(res) {
            hideLoading();

            if (!res.data || res.data.length === 0) {
                $('#pavementResults').hide();
                $('#pavementEmptyState').show();
                $('#pavementEmptyState h3').text('No Data Found');
                return;
            }

            // Cache the result
            setPavementCache(res);

            // Render data
            renderPavementFromData(res);
        })
        .fail(function(xhr) {
            hideLoading();
            $('#pavementEmptyState').show();
            $('#pavementResults').hide();
        });
}

function renderPavementFromData(res) {
    // Update date range display
    const fromDate = res.summary.from_date || '2020-01-01';
    const toDate = res.summary.to_date || '2025-12-31';
    $('#pavementDateRange').text(`Data from ${formatDate(fromDate)} to ${formatDate(toDate)} | Hot Days: ${res.summary.hot_days} | Cold Days: ${res.summary.cold_days}`);

    // Update the date inputs with the actual range used
    if (res.summary.from_date) $('#pavementFromDate').val(res.summary.from_date);
    if (res.summary.to_date) $('#pavementToDate').val(res.summary.to_date);

    // Render summary
    renderPavementSummary(res.summary);

    // Render table
    renderPavementStatsTable(res.data);

    $('#pavementResults').show();
    $('#pavementEmptyState').hide();
}

function runPavementAnalysis() {
    const selectedStations = [];
    $('.pavement-station-cb:checked').each(function() {
        selectedStations.push($(this).val());
    });

    if (selectedStations.length === 0) {
        alert('Please select at least one station');
        return;
    }

    showLoading();

    const fromDate = $('#pavementFromDate').val();
    const toDate = $('#pavementToDate').val();

    // Build parameters
    const params = new URLSearchParams();
    selectedStations.forEach(id => params.append('stations[]', id));
    params.append('from_date', fromDate);
    params.append('to_date', toDate);
    params.append('hot_days', $('#pavementHotDays').val());
    params.append('cold_days', $('#pavementColdDays').val());
    params.append('sd_type', $('#pavementSdType').val());

    $.get("{{ route('admin.daily-weathers.pavementAnalysisData') }}?" + params.toString())
        .done(function(res) {
            hideLoading();

            if (!res.data || res.data.length === 0) {
                $('#pavementResults').hide();
                $('#pavementEmptyState').show();
                $('#pavementEmptyState h3').text('No Data Found');
                return;
            }

            // Update date range display
            $('#pavementDateRange').text(`Data from ${formatDate(fromDate)} to ${formatDate(toDate)} | Hot Days: ${res.summary.hot_days} | Cold Days: ${res.summary.cold_days}`);

            // Render summary
            renderPavementSummary(res.summary);

            // Render table
            renderPavementStatsTable(res.data);

            $('#pavementResults').show();
            $('#pavementEmptyState').hide();
        })
        .fail(function(xhr) {
            hideLoading();
            const msg = xhr.responseJSON?.message || 'Failed to load analysis data. Please try again.';
            alert(msg);
        });
}

function renderPavementSummary(summary) {
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
                <h4>98% Max Air Temp</h4>
                <div class="summary-stat-value">${summary.overall_max_air || '-'}<span>°C</span></div>
            </div>
        </div>
        <div class="summary-stat-card cold">
            <div class="summary-stat-icon"><i class="ri-temp-cold-line"></i></div>
            <div class="summary-stat-info">
                <h4>98% Min Air Temp</h4>
                <div class="summary-stat-value">${summary.overall_min_air || '-'}<span>°C</span></div>
            </div>
        </div>
        <div class="summary-stat-card pvt">
            <div class="summary-stat-icon"><i class="ri-road-line"></i></div>
            <div class="summary-stat-info">
                <h4>98% Max PVT</h4>
                <div class="summary-stat-value">${summary.overall_max_pvt || '-'}<span>°C</span></div>
            </div>
        </div>
    `;
    $('#pavementSummary').html(html);
}

function renderPavementStatsTable(data) {
    // Store data globally for modal access
    pavementAnalysisData = data;

    let tbody = '';
    data.forEach((row, index) => {
        tbody += `
            <tr onclick="openPavementDetailModal(${index})" title="Click to view details">
                <td class="col-station">${escapeHtml(row.station_name)}</td>
                <td>${row.lon}</td>
                <td>${row.lat}</td>
                <td>${row.elev}</td>
                <td class="val-low">${row.avg_low}°C</td>
                <td>${row.std_low}</td>
                <td class="val-high">${row.avg_high}°C</td>
                <td>${row.std_high}</td>
                <td class="val-high">${row.max_air_50}°C</td>
                <td class="val-pvt">${row.max_pvt_50}°C</td>
                <td class="val-low">${row.min_air_50}°C</td>
                <td class="val-pvt">${row.min_pvt_50}°C</td>
                <td class="val-high">${row.max_air_98}°C</td>
                <td class="val-pvt">${row.max_pvt_98}°C</td>
                <td class="val-low">${row.min_air_98}°C</td>
                <td class="val-pvt">${row.min_pvt_98}°C</td>
            </tr>
        `;
    });
    $('#pavementStatsTableBody').html(tbody);

    // Add click hint below table
    if (!$('.click-hint').length) {
        $('.table-legend').after('<div class="click-hint"><i class="ri-cursor-line"></i> Click on any row to view detailed analysis and graphs</div>');
    }

    // Remove inline chart section if exists
    $('#inlineChartSection').remove();
}

function exportPavementCSV() {
    const table = document.getElementById('pavementStatsTable');
    if (!table) return;

    let csv = [];

    // Get all header rows
    const headerRows = table.querySelectorAll('thead tr');

    // Create merged header for CSV
    csv.push('"Station","Long","Lat","Elev (m)","Avg Low","Std Low","Avg High","Std High","50% Max Air","50% Max PVT","50% Min Air","50% Min PVT","98% Max Air","98% Max PVT","98% Min Air","98% Min PVT"');

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
    link.download = 'pavement_analysis_' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}

function printPavementAnalysis() {
    const printContent = document.getElementById('pavementResults').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Pavement Temperature Analysis Report</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 20px; }
                .analysis-summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
                .summary-stat-card { padding: 16px; border: 1px solid #ddd; border-radius: 8px; }
                .summary-stat-info h4 { margin: 0 0 4px; font-size: 11px; color: #666; text-transform: uppercase; }
                .summary-stat-value { font-size: 24px; font-weight: 700; }
                .analysis-table-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
                .table-card-header { padding: 12px 16px; background: #f5f5f5; }
                .analysis-stats-table { width: 100%; border-collapse: collapse; }
                .analysis-stats-table th, .analysis-stats-table td { padding: 8px 10px; border: 1px solid #ddd; text-align: center; font-size: 11px; }
                .analysis-stats-table th { background: #f5f5f5; }
                .analysis-stats-table .header-main th { background: #333; color: white; }
                .val-high { color: #D83B01; font-weight: 600; }
                .val-low { color: #0078D4; font-weight: 600; }
                .val-pvt { color: #8764B8; }
                .table-legend, .header-actions, .summary-stat-icon { display: none; }
                .formula-note { margin-top: 16px; padding: 12px; background: #f5f5f5; border-radius: 4px; font-size: 12px; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <h1 style="text-align: center; margin-bottom: 24px;">Pavement Temperature Analysis Report (SUPERPAVE)</h1>
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

// ==================== PAVEMENT DETAIL MODAL ====================
function openPavementDetailModal(index) {
    const data = pavementAnalysisData[index];
    if (!data) return;

    // Update modal title
    $('#pavementDetailTitle').text(data.station_name);
    $('#pavementDetailSubtitle').text(`Lat: ${data.lat}° | Lon: ${data.lon}° | Elev: ${data.elev}m`);

    // Reset to first tab
    $('.pavement-tab-btn').removeClass('active').first().addClass('active');
    $('.pavement-tab-content').removeClass('active').first().addClass('active');

    // Render chart
    renderPavementDetailChart(data);

    // Render formula explanation
    renderPavementFormulaExplanation(data);

    // Open modal
    openModal('pavementDetailModal');
}

// Tab switching
$(document).on('click', '.pavement-tab-btn', function() {
    const tab = $(this).data('tab');
    $('.pavement-tab-btn').removeClass('active');
    $(this).addClass('active');
    $('.pavement-tab-content').removeClass('active');
    $(`#pavementTab${tab.charAt(0).toUpperCase() + tab.slice(1)}`).addClass('active');
});

let highTempBellChart = null;
let lowTempBellChart = null;

function renderPavementDetailChart(data) {
    const avgHigh = data.avg_high;
    const avgLow = data.avg_low;
    const stdHigh = data.std_high || 1;
    const stdLow = data.std_low || 1;
    const yearlyData = data.yearly_data || [];
    const hotDays = $('#pavementHotDays').val() || 7;
    const coldDays = $('#pavementColdDays').val() || 1;

    // Update subtitles
    $('#highTempSubtitle').text(`${hotDays}-day average per year (${yearlyData.length} years of data)`);
    $('#lowTempSubtitle').text(`${coldDays}-day coldest per year (${yearlyData.length} years of data)`);

    // Render High Temperature Bell Curve with data
    renderBellCurve('highTempBellCurve', avgHigh, stdHigh, yearlyData, 'high_avg', '#D83B01', 'high');

    // Render Low Temperature Bell Curve with data
    renderBellCurve('lowTempBellCurve', avgLow, stdLow, yearlyData, 'low_avg', '#0078D4', 'low');

    // Render data point chips
    renderDataPointChips('highTempDataTable', yearlyData, 'high_avg', avgHigh, stdHigh);
    renderDataPointChips('lowTempDataTable', yearlyData, 'low_avg', avgLow, stdLow);

    // Count data in each band
    const highIn1SD = yearlyData.filter(d => Math.abs(d.high_avg - avgHigh) <= stdHigh).length;
    const highIn2SD = yearlyData.filter(d => Math.abs(d.high_avg - avgHigh) <= 2*stdHigh).length;
    const lowIn1SD = yearlyData.filter(d => Math.abs(d.low_avg - avgLow) <= stdLow).length;
    const lowIn2SD = yearlyData.filter(d => Math.abs(d.low_avg - avgLow) <= 2*stdLow).length;

    // Update stats with more details
    $('#highTempStats').html(`
        <div class="bell-stat-item">
            <div class="stat-label">Mean (μ)</div>
            <div class="stat-value">${avgHigh}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">Std Dev (σ)</div>
            <div class="stat-value">±${stdHigh}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">In ±1σ</div>
            <div class="stat-value">${highIn1SD}/${yearlyData.length}</div>
            <div class="stat-range">${((highIn1SD/yearlyData.length)*100).toFixed(0)}% (expect 68%)</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">In ±2σ</div>
            <div class="stat-value">${highIn2SD}/${yearlyData.length}</div>
            <div class="stat-range">${((highIn2SD/yearlyData.length)*100).toFixed(0)}% (expect 95%)</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">-2σ Value</div>
            <div class="stat-value">${(avgHigh - 2*stdHigh).toFixed(1)}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">+2σ Value</div>
            <div class="stat-value">${(avgHigh + 2*stdHigh).toFixed(1)}°C</div>
        </div>
    `);

    $('#lowTempStats').html(`
        <div class="bell-stat-item">
            <div class="stat-label">Mean (μ)</div>
            <div class="stat-value">${avgLow}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">Std Dev (σ)</div>
            <div class="stat-value">±${stdLow}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">In ±1σ</div>
            <div class="stat-value">${lowIn1SD}/${yearlyData.length}</div>
            <div class="stat-range">${((lowIn1SD/yearlyData.length)*100).toFixed(0)}% (expect 68%)</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">In ±2σ</div>
            <div class="stat-value">${lowIn2SD}/${yearlyData.length}</div>
            <div class="stat-range">${((lowIn2SD/yearlyData.length)*100).toFixed(0)}% (expect 95%)</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">-2σ Value</div>
            <div class="stat-value">${(avgLow - 2*stdLow).toFixed(1)}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">+2σ Value</div>
            <div class="stat-value">${(avgLow + 2*stdLow).toFixed(1)}°C</div>
        </div>
    `);

    // Render yearly data table
    renderYearlyDataTable(yearlyData, avgHigh, stdHigh, avgLow, stdLow);
}

function renderDataPointChips(containerId, yearlyData, key, mean, std) {
    let html = '<div class="data-points-grid">';
    yearlyData.forEach(d => {
        const val = d[key];
        const deviation = Math.abs(val - mean) / std;
        let chipClass = 'outside-2sd';
        if (deviation <= 1) chipClass = 'within-1sd';
        else if (deviation <= 2) chipClass = 'within-2sd';

        const devFromMean = (val - mean).toFixed(2);
        const devSign = devFromMean >= 0 ? '+' : '';

        html += `<div class="data-point-chip ${chipClass}" title="${d.year}: ${val}°C (${devSign}${devFromMean} from μ, ${(deviation).toFixed(2)}σ)">
            <span class="year">${d.year}:</span>
            <span class="value">${val}°C</span>
        </div>`;
    });
    html += '</div>';
    $(`#${containerId}`).html(html);
}

function renderYearlyDataTable(yearlyData, avgHigh, stdHigh, avgLow, stdLow) {
    let html = `
        <div class="yearly-data-header">
            <h5><i class="ri-table-line"></i> Yearly Data Points (${yearlyData.length} years)</h5>
        </div>
        <div style="max-height: 200px; overflow-y: auto;">
        <table class="yearly-data-table">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>High Temp</th>
                    <th>High Dev</th>
                    <th>High σ</th>
                    <th>Low Temp</th>
                    <th>Low Dev</th>
                    <th>Low σ</th>
                </tr>
            </thead>
            <tbody>
    `;

    yearlyData.forEach(d => {
        const highDev = (d.high_avg - avgHigh).toFixed(2);
        const highSigma = (Math.abs(d.high_avg - avgHigh) / stdHigh).toFixed(2);
        const lowDev = (d.low_avg - avgLow).toFixed(2);
        const lowSigma = (Math.abs(d.low_avg - avgLow) / stdLow).toFixed(2);

        html += `
            <tr>
                <td><strong>${d.year}</strong></td>
                <td class="val-high">${d.high_avg}°C</td>
                <td class="deviation ${highDev >= 0 ? 'positive' : 'negative'}">${highDev >= 0 ? '+' : ''}${highDev}°C</td>
                <td>${highSigma}σ</td>
                <td class="val-low">${d.low_avg}°C</td>
                <td class="deviation ${lowDev >= 0 ? 'positive' : 'negative'}">${lowDev >= 0 ? '+' : ''}${lowDev}°C</td>
                <td>${lowSigma}σ</td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    $('#yearlyDataSection').html(html);
}

function renderBellCurve(canvasId, mean, std, yearlyData, key, color, type) {
    const container = document.getElementById(canvasId).parentElement;
    if (!container) return;

    const width = container.offsetWidth || 400;
    const height = 280;
    const chartId = canvasId; // Use canvasId as chartId for click handlers

    // Extract and analyze data points
    const dataPoints = yearlyData.map(d => ({
        year: d.year,
        value: parseFloat(d[key]),
        sigma: (parseFloat(d[key]) - mean) / std
    })).sort((a, b) => a.year - b.year);

    const totalPoints = dataPoints.length;
    const values = dataPoints.map(d => d.value);

    // Advanced statistics
    const minVal = Math.min(...values);
    const maxVal = Math.max(...values);
    const range = maxVal - minVal;
    const sortedVals = [...values].sort((a, b) => a - b);
    const median = sortedVals.length % 2 === 0
        ? (sortedVals[sortedVals.length/2 - 1] + sortedVals[sortedVals.length/2]) / 2
        : sortedVals[Math.floor(sortedVals.length/2)];
    const q1 = sortedVals[Math.floor(sortedVals.length * 0.25)];
    const q3 = sortedVals[Math.floor(sortedVals.length * 0.75)];
    const iqr = q3 - q1;

    // Trend analysis (linear regression)
    const n = dataPoints.length;
    const sumX = dataPoints.reduce((s, d, i) => s + i, 0);
    const sumY = dataPoints.reduce((s, d) => s + d.value, 0);
    const sumXY = dataPoints.reduce((s, d, i) => s + i * d.value, 0);
    const sumX2 = dataPoints.reduce((s, d, i) => s + i * i, 0);
    const slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
    const trendDirection = slope > 0.1 ? 'increasing' : slope < -0.1 ? 'decreasing' : 'stable';
    const trendPerYear = slope.toFixed(2);

    // Band counts
    const inBand = (s1, s2) => dataPoints.filter(p => p.sigma >= s1 && p.sigma < s2).length;
    const bandCounts = {
        left3: inBand(-Infinity, -3), left2to3: inBand(-3, -2), left1to2: inBand(-2, -1),
        left0to1: inBand(-1, 0), right0to1: inBand(0, 1), right1to2: inBand(1, 2),
        right2to3: inBand(2, 3), right3: inBand(3, Infinity)
    };
    const within1 = bandCounts.left0to1 + bandCounts.right0to1;
    const within2 = within1 + bandCounts.left1to2 + bandCounts.right1to2;
    const within3 = within2 + bandCounts.left2to3 + bandCounts.right2to3;

    const pct = (c) => totalPoints > 0 ? ((c / totalPoints) * 100).toFixed(1) : '0';

    // Outliers (beyond 2σ)
    const outliers = dataPoints.filter(p => Math.abs(p.sigma) > 2);

    // Chart setup
    const padding = { top: 45, right: 25, bottom: 55, left: 25 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;

    const colors = {
        band1: type === 'high' ? '#D83B01' : '#0078D4',
        band2: type === 'high' ? '#FF8C00' : '#50B4FF',
        band3: type === 'high' ? '#FFB366' : '#A0D4FF',
        line: type === 'high' ? '#B22000' : '#005A9E',
        text: '#333', axis: '#666',
        highlight: type === 'high' ? '#FF4500' : '#00BFFF'
    };

    const gaussian = (x) => Math.exp(-0.5 * Math.pow(x, 2));
    const xToSvg = (sigma) => padding.left + ((sigma + 4) / 8) * chartWidth;
    const yToSvg = (y) => padding.top + chartHeight - (y * chartHeight);

    const bandPath = (fromS, toS) => {
        let d = `M ${xToSvg(fromS)} ${yToSvg(0)}`;
        for (let s = fromS; s <= toS; s += 0.02) d += ` L ${xToSvg(s)} ${yToSvg(gaussian(s))}`;
        return d + ` L ${xToSvg(toS)} ${yToSvg(0)} Z`;
    };

    let curvePath = `M ${xToSvg(-4)} ${yToSvg(gaussian(-4))}`;
    for (let s = -4; s <= 4; s += 0.02) curvePath += ` L ${xToSvg(s)} ${yToSvg(gaussian(s))}`;

    // Store data globally for click handlers
    window[`bellData_${chartId}`] = {
        mean, std, dataPoints, type, key, colors, totalPoints,
        minVal, maxVal, range, median, q1, q3, iqr,
        slope, trendDirection, trendPerYear, outliers,
        bandCounts, within1, within2, within3, pct
    };

    // Build SVG
    let svg = `
    <svg id="svg_${chartId}" viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg"
         style="width:100%;height:100%;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;cursor:crosshair;">
        <defs>
            <linearGradient id="band1Grad${chartId}" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color:${colors.band1};stop-opacity:0.8"/>
                <stop offset="100%" style="stop-color:${colors.band1};stop-opacity:0.5"/>
            </linearGradient>
            <linearGradient id="band2Grad${chartId}" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color:${colors.band2};stop-opacity:0.7"/>
                <stop offset="100%" style="stop-color:${colors.band2};stop-opacity:0.4"/>
            </linearGradient>
            <linearGradient id="band3Grad${chartId}" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color:${colors.band3};stop-opacity:0.6"/>
                <stop offset="100%" style="stop-color:${colors.band3};stop-opacity:0.3"/>
            </linearGradient>
            <filter id="glow${chartId}" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur stdDeviation="3" result="blur"/>
                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
        </defs>

        <!-- Interactive summary box -->
        <g onclick="showBellAnalytics('${chartId}', 'summary')" style="cursor:pointer">
            <rect x="${padding.left}" y="5" width="${chartWidth}" height="28" rx="4" fill="#f8f9fa" stroke="#e0e0e0" stroke-width="1"/>
            <text x="${padding.left + 10}" y="23" font-size="10" fill="#333">
                <tspan font-weight="bold">n=${totalPoints}</tspan>
                <tspan dx="8">±1σ:</tspan><tspan font-weight="bold" fill="${colors.band1}"> ${within1} (${pct(within1)}%)</tspan>
                <tspan dx="8">±2σ:</tspan><tspan font-weight="bold" fill="${colors.band2}"> ${within2} (${pct(within2)}%)</tspan>
                <tspan dx="8">📊</tspan>
            </text>
            <title>Click for detailed statistics</title>
        </g>

        <!-- Clickable bands -->
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:-4,to:-3,name:'Beyond -3σ'})" style="cursor:pointer">
            <path d="${bandPath(-4, -3)}" fill="url(#band3Grad${chartId})"/>
        </g>
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:3,to:4,name:'Beyond +3σ'})" style="cursor:pointer">
            <path d="${bandPath(3, 4)}" fill="url(#band3Grad${chartId})"/>
        </g>
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:-3,to:-2,name:'-3σ to -2σ'})" style="cursor:pointer">
            <path d="${bandPath(-3, -2)}" fill="url(#band3Grad${chartId})"/>
        </g>
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:2,to:3,name:'+2σ to +3σ'})" style="cursor:pointer">
            <path d="${bandPath(2, 3)}" fill="url(#band3Grad${chartId})"/>
        </g>
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:-2,to:-1,name:'-2σ to -1σ'})" style="cursor:pointer">
            <path d="${bandPath(-2, -1)}" fill="url(#band2Grad${chartId})"/>
        </g>
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:1,to:2,name:'+1σ to +2σ'})" style="cursor:pointer">
            <path d="${bandPath(1, 2)}" fill="url(#band2Grad${chartId})"/>
        </g>
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:-1,to:0,name:'-1σ to Mean'})" style="cursor:pointer">
            <path d="${bandPath(-1, 0)}" fill="url(#band1Grad${chartId})"/>
        </g>
        <g onclick="showBellAnalytics('${chartId}', 'band', {from:0,to:1,name:'Mean to +1σ'})" style="cursor:pointer">
            <path d="${bandPath(0, 1)}" fill="url(#band1Grad${chartId})"/>
        </g>

        <!-- Vertical σ lines -->
        ${[-3, -2, -1, 1, 2, 3].map(s => `
            <line x1="${xToSvg(s)}" y1="${yToSvg(gaussian(s))}" x2="${xToSvg(s)}" y2="${yToSvg(0)}" stroke="#999" stroke-width="1" stroke-dasharray="4,3"/>
        `).join('')}

        <!-- Mean line (clickable) -->
        <g onclick="showBellAnalytics('${chartId}', 'mean')" style="cursor:pointer">
            <line x1="${xToSvg(0)}" y1="${yToSvg(gaussian(0))}" x2="${xToSvg(0)}" y2="${yToSvg(0)}" stroke="${colors.line}" stroke-width="2"/>
            <polygon points="${xToSvg(0)},${yToSvg(gaussian(0))-3} ${xToSvg(0)-5},${yToSvg(gaussian(0))-10} ${xToSvg(0)+5},${yToSvg(gaussian(0))-10}" fill="${colors.line}"/>
            <text x="${xToSvg(0)}" y="${yToSvg(gaussian(0))-14}" text-anchor="middle" font-size="8" font-weight="bold" fill="${colors.line}">MEAN</text>
        </g>

        <!-- Bell curve -->
        <path d="${curvePath}" fill="none" stroke="${colors.line}" stroke-width="2.5" stroke-linecap="round"/>

        <!-- X-axis -->
        <line x1="${padding.left}" y1="${yToSvg(0)}" x2="${width - padding.right}" y2="${yToSvg(0)}" stroke="${colors.axis}" stroke-width="1"/>

        <!-- Band labels -->
        ${bandCounts.left0to1 > 0 ? `<text x="${xToSvg(-0.5)}" y="${yToSvg(gaussian(-0.5)*0.5)}" text-anchor="middle" font-size="11" font-weight="bold" fill="#fff">${bandCounts.left0to1}</text>` : ''}
        ${bandCounts.right0to1 > 0 ? `<text x="${xToSvg(0.5)}" y="${yToSvg(gaussian(0.5)*0.5)}" text-anchor="middle" font-size="11" font-weight="bold" fill="#fff">${bandCounts.right0to1}</text>` : ''}
        ${bandCounts.left1to2 > 0 ? `<text x="${xToSvg(-1.5)}" y="${yToSvg(gaussian(-1.5)*0.55)}" text-anchor="middle" font-size="10" font-weight="bold" fill="#fff">${bandCounts.left1to2}</text>` : ''}
        ${bandCounts.right1to2 > 0 ? `<text x="${xToSvg(1.5)}" y="${yToSvg(gaussian(1.5)*0.55)}" text-anchor="middle" font-size="10" font-weight="bold" fill="#fff">${bandCounts.right1to2}</text>` : ''}
        ${bandCounts.left2to3 > 0 ? `<text x="${xToSvg(-2.5)}" y="${yToSvg(gaussian(-2.5)*0.6)-5}" text-anchor="middle" font-size="9" font-weight="bold" fill="#555">${bandCounts.left2to3}</text>` : ''}
        ${bandCounts.right2to3 > 0 ? `<text x="${xToSvg(2.5)}" y="${yToSvg(gaussian(2.5)*0.6)-5}" text-anchor="middle" font-size="9" font-weight="bold" fill="#555">${bandCounts.right2to3}</text>` : ''}

        <!-- X-axis labels -->
        ${[
            { s: -3, label: '-3σ', value: (mean - 3*std).toFixed(1) },
            { s: -2, label: '-2σ', value: (mean - 2*std).toFixed(1) },
            { s: -1, label: '-1σ', value: (mean - std).toFixed(1) },
            { s: 0, label: 'μ', value: mean.toFixed(1) },
            { s: 1, label: '+1σ', value: (mean + std).toFixed(1) },
            { s: 2, label: '+2σ', value: (mean + 2*std).toFixed(1) },
            { s: 3, label: '+3σ', value: (mean + 3*std).toFixed(1) }
        ].map(item => `
            <text x="${xToSvg(item.s)}" y="${yToSvg(0) + 14}" text-anchor="middle" font-size="10" font-weight="bold" fill="${item.s === 0 ? colors.line : colors.text}">${item.label}</text>
            <text x="${xToSvg(item.s)}" y="${yToSvg(0) + 26}" text-anchor="middle" font-size="9" fill="#666">${item.value}°C</text>
        `).join('')}

        <!-- Interactive data points -->
        ${dataPoints.filter(p => p.sigma >= -4 && p.sigma <= 4).map((point, i) => {
            const cx = xToSvg(point.sigma);
            const cy = yToSvg(gaussian(point.sigma));
            const isOutlier = Math.abs(point.sigma) > 2;
            return `
            <g onclick="showBellAnalytics('${chartId}', 'point', ${JSON.stringify(point).replace(/"/g, "'")})" style="cursor:pointer">
                <circle cx="${cx}" cy="${cy}" r="12" fill="transparent"/>
                <circle cx="${cx}" cy="${cy}" r="${isOutlier ? 8 : 6}" fill="${isOutlier ? colors.highlight : '#fff'}"
                        stroke="${colors.line}" stroke-width="2.5"/>
                <text x="${cx}" y="${cy - 12}" text-anchor="middle" font-size="9" font-weight="bold" fill="${colors.text}">${point.year}</text>
                ${isOutlier ? `<text x="${cx + 10}" y="${cy - 5}" font-size="8" fill="${colors.highlight}">⚠</text>` : ''}
            </g>
        `}).join('')}

        <!-- Trend indicator -->
        <g onclick="showBellAnalytics('${chartId}', 'trend')" style="cursor:pointer">
            <rect x="${width - 70}" y="8" width="60" height="22" rx="3" fill="${trendDirection === 'increasing' ? '#fff3e0' : trendDirection === 'decreasing' ? '#e3f2fd' : '#f5f5f5'}" stroke="#ddd"/>
            <text x="${width - 40}" y="23" text-anchor="middle" font-size="9" fill="${trendDirection === 'increasing' ? '#e65100' : trendDirection === 'decreasing' ? '#1565c0' : '#666'}">
                ${trendDirection === 'increasing' ? '📈 +' : trendDirection === 'decreasing' ? '📉 ' : '➡️ '}${Math.abs(trendPerYear)}°C/yr
            </text>
        </g>

        <!-- Info hint -->
        <text x="${width - padding.right}" y="${yToSvg(0) + 42}" text-anchor="end" font-size="8" fill="#999">Click any element for details</text>
    </svg>`;

    container.innerHTML = svg;

    if (type === 'high') highTempBellChart = { destroy: () => {} };
    else lowTempBellChart = { destroy: () => {} };
}

// Analytics popup handler
function showBellAnalytics(chartId, type, data = null) {
    const bellData = window[`bellData_${chartId}`];
    if (!bellData) return;

    let content = '';
    let title = '';

    if (type === 'summary') {
        title = `📊 Statistical Summary (${bellData.type === 'high' ? 'High Temp' : 'Low Temp'})`;
        content = `
            <div class="analytics-grid">
                <div class="analytics-section">
                    <h4>📈 Central Tendency</h4>
                    <div class="stat-row"><span>Mean (μ):</span><strong>${bellData.mean.toFixed(2)}°C</strong></div>
                    <div class="stat-row"><span>Median:</span><strong>${bellData.median.toFixed(2)}°C</strong></div>
                    <div class="stat-row"><span>Std Dev (σ):</span><strong>${bellData.std.toFixed(2)}°C</strong></div>
                </div>
                <div class="analytics-section">
                    <h4>📊 Dispersion</h4>
                    <div class="stat-row"><span>Min:</span><strong>${bellData.minVal.toFixed(2)}°C</strong></div>
                    <div class="stat-row"><span>Max:</span><strong>${bellData.maxVal.toFixed(2)}°C</strong></div>
                    <div class="stat-row"><span>Range:</span><strong>${bellData.range.toFixed(2)}°C</strong></div>
                    <div class="stat-row"><span>IQR:</span><strong>${bellData.iqr.toFixed(2)}°C</strong></div>
                </div>
                <div class="analytics-section">
                    <h4>📉 Distribution</h4>
                    <div class="stat-row"><span>Within ±1σ:</span><strong>${bellData.within1}/${bellData.totalPoints} (${bellData.pct(bellData.within1)}%)</strong></div>
                    <div class="stat-row"><span>Within ±2σ:</span><strong>${bellData.within2}/${bellData.totalPoints} (${bellData.pct(bellData.within2)}%)</strong></div>
                    <div class="stat-row"><span>Outliers (>2σ):</span><strong>${bellData.outliers.length}</strong></div>
                </div>
                <div class="analytics-section">
                    <h4>🎯 Quartiles</h4>
                    <div class="stat-row"><span>Q1 (25%):</span><strong>${bellData.q1.toFixed(2)}°C</strong></div>
                    <div class="stat-row"><span>Q2 (50%):</span><strong>${bellData.median.toFixed(2)}°C</strong></div>
                    <div class="stat-row"><span>Q3 (75%):</span><strong>${bellData.q3.toFixed(2)}°C</strong></div>
                </div>
            </div>
            <div class="analytics-section full-width">
                <h4>🔮 98% Reliability Values (for PG Grade)</h4>
                <div class="reliability-box">
                    <div class="rel-item">
                        <span>98% Max:</span>
                        <strong class="high">${(bellData.mean + 2.054 * bellData.std).toFixed(1)}°C</strong>
                    </div>
                    <div class="rel-item">
                        <span>98% Min:</span>
                        <strong class="low">${(bellData.mean - 2.054 * bellData.std).toFixed(1)}°C</strong>
                    </div>
                </div>
            </div>`;
    } else if (type === 'point') {
        const point = data;
        const deviation = (point.value - bellData.mean).toFixed(2);
        const percentile = calculatePercentile(point.sigma);
        const prevYear = bellData.dataPoints.find(p => p.year === point.year - 1);
        const nextYear = bellData.dataPoints.find(p => p.year === point.year + 1);

        title = `📍 Year ${point.year} Analysis`;
        content = `
            <div class="analytics-grid">
                <div class="analytics-section highlight">
                    <h4>🌡️ Temperature</h4>
                    <div class="big-value">${point.value}°C</div>
                    <div class="stat-row"><span>Deviation:</span><strong>${deviation >= 0 ? '+' : ''}${deviation}°C</strong></div>
                    <div class="stat-row"><span>Position:</span><strong>${point.sigma.toFixed(2)}σ</strong></div>
                    <div class="stat-row"><span>Percentile:</span><strong>${percentile}%</strong></div>
                </div>
                <div class="analytics-section">
                    <h4>📊 Comparison</h4>
                    <div class="stat-row"><span>vs Mean:</span><strong class="${point.value > bellData.mean ? 'high' : 'low'}">${point.value > bellData.mean ? 'Above' : 'Below'} avg</strong></div>
                    <div class="stat-row"><span>Rank:</span><strong>${getRank(point.value, bellData.dataPoints)} of ${bellData.totalPoints}</strong></div>
                    ${Math.abs(point.sigma) > 2 ? '<div class="outlier-badge">⚠️ OUTLIER</div>' : ''}
                </div>
                <div class="analytics-section">
                    <h4>📈 Year-over-Year</h4>
                    ${prevYear ? `<div class="stat-row"><span>${prevYear.year}:</span><strong>${prevYear.value}°C (${(point.value - prevYear.value) >= 0 ? '+' : ''}${(point.value - prevYear.value).toFixed(2)}°C)</strong></div>` : ''}
                    <div class="stat-row current"><span>${point.year}:</span><strong>${point.value}°C</strong></div>
                    ${nextYear ? `<div class="stat-row"><span>${nextYear.year}:</span><strong>${nextYear.value}°C (${(nextYear.value - point.value) >= 0 ? '+' : ''}${(nextYear.value - point.value).toFixed(2)}°C)</strong></div>` : ''}
                </div>
            </div>
            <div class="analytics-section full-width">
                <h4>🛣️ Engineering Implication</h4>
                <p>${getEngineeringImplication(point, bellData)}</p>
            </div>`;
    } else if (type === 'trend') {
        title = `📈 Trend Analysis`;
        const years = bellData.dataPoints.map(p => p.year);
        content = `
            <div class="analytics-grid">
                <div class="analytics-section highlight">
                    <h4>📊 Trend Direction</h4>
                    <div class="trend-indicator ${bellData.trendDirection}">
                        ${bellData.trendDirection === 'increasing' ? '🔺 INCREASING' : bellData.trendDirection === 'decreasing' ? '🔻 DECREASING' : '➡️ STABLE'}
                    </div>
                    <div class="stat-row"><span>Rate:</span><strong>${bellData.trendPerYear}°C/year</strong></div>
                </div>
                <div class="analytics-section">
                    <h4>📅 Period</h4>
                    <div class="stat-row"><span>From:</span><strong>${Math.min(...years)}</strong></div>
                    <div class="stat-row"><span>To:</span><strong>${Math.max(...years)}</strong></div>
                    <div class="stat-row"><span>Years:</span><strong>${bellData.totalPoints}</strong></div>
                </div>
            </div>
            <div class="analytics-section full-width">
                <h4>🔮 5-Year Projection</h4>
                <div class="projection-box">
                    <span>If trend continues:</span>
                    <strong>${(bellData.mean + bellData.slope * 5).toFixed(1)}°C by ${Math.max(...years) + 5}</strong>
                </div>
                <p class="warning">${bellData.trendDirection === 'increasing' && bellData.type === 'high' ? '⚠️ Rising temperatures may require upgrading PG binder grade in future projects.' : bellData.trendDirection === 'decreasing' && bellData.type === 'low' ? '⚠️ Decreasing low temperatures may increase cracking risk.' : '✅ Temperature trend is within normal variation.'}</p>
            </div>`;
    } else if (type === 'band') {
        const bandPoints = bellData.dataPoints.filter(p => p.sigma >= data.from && p.sigma < data.to);
        title = `📊 Band: ${data.name}`;
        content = `
            <div class="analytics-section">
                <h4>📈 Band Statistics</h4>
                <div class="stat-row"><span>Data Points:</span><strong>${bandPoints.length} (${bellData.pct(bandPoints.length)}%)</strong></div>
                <div class="stat-row"><span>Temp Range:</span><strong>${(bellData.mean + data.from * bellData.std).toFixed(1)}°C to ${(bellData.mean + data.to * bellData.std).toFixed(1)}°C</strong></div>
            </div>
            ${bandPoints.length > 0 ? `
            <div class="analytics-section full-width">
                <h4>📅 Years in this band</h4>
                <div class="year-chips">
                    ${bandPoints.map(p => `<span class="year-chip">${p.year}: ${p.value}°C</span>`).join('')}
                </div>
            </div>` : '<p>No data points in this band.</p>'}`;
    } else if (type === 'mean') {
        title = `📍 Mean (μ) Details`;
        content = `
            <div class="analytics-grid">
                <div class="analytics-section highlight">
                    <h4>🎯 Mean Value</h4>
                    <div class="big-value">${bellData.mean.toFixed(2)}°C</div>
                </div>
                <div class="analytics-section">
                    <h4>📊 Confidence Intervals</h4>
                    <div class="stat-row"><span>68% CI:</span><strong>${(bellData.mean - bellData.std).toFixed(1)}°C to ${(bellData.mean + bellData.std).toFixed(1)}°C</strong></div>
                    <div class="stat-row"><span>95% CI:</span><strong>${(bellData.mean - 2*bellData.std).toFixed(1)}°C to ${(bellData.mean + 2*bellData.std).toFixed(1)}°C</strong></div>
                    <div class="stat-row"><span>99% CI:</span><strong>${(bellData.mean - 3*bellData.std).toFixed(1)}°C to ${(bellData.mean + 3*bellData.std).toFixed(1)}°C</strong></div>
                </div>
            </div>`;
    } else if (type === 'formula') {
        const isHigh = bellData.type === 'high';
        const reliability98 = isHigh ? (bellData.mean + 2.054 * bellData.std) : (bellData.mean - 2.054 * bellData.std);

        title = `📐 Formula & Methodology`;
        content = `
            <div class="formula-hero">
                <div class="formula-hero-badge">${isHigh ? '🔥 HIGH TEMP' : '❄️ LOW TEMP'}</div>
                <div class="formula-hero-title">SUPERPAVE Performance Grade Selection</div>
                <div class="formula-hero-subtitle">SHRP-A-648A Methodology</div>
            </div>

            <div class="formula-visual-section">
                <h4><i class="fas fa-calculator"></i> Statistical Formula</h4>
                <div class="formula-card">
                    <div class="formula-equation">
                        <span class="formula-var">T</span><sub>98%</sub> =
                        <span class="formula-var">μ</span> ${isHigh ? '+' : '−'}
                        <span class="formula-const">2.054</span> ×
                        <span class="formula-var">σ</span>
                    </div>
                    <div class="formula-meaning">
                        <div class="formula-meaning-item">
                            <span class="formula-symbol">T<sub>98%</sub></span>
                            <span class="formula-desc">Design temperature at 98% reliability</span>
                        </div>
                        <div class="formula-meaning-item">
                            <span class="formula-symbol">μ</span>
                            <span class="formula-desc">Mean (average) temperature</span>
                        </div>
                        <div class="formula-meaning-item">
                            <span class="formula-symbol">σ</span>
                            <span class="formula-desc">Standard deviation</span>
                        </div>
                        <div class="formula-meaning-item">
                            <span class="formula-symbol">2.054</span>
                            <span class="formula-desc">Z-score for 98% confidence (one-tailed)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="formula-visual-section">
                <h4><i class="fas fa-shoe-prints"></i> Step-by-Step Calculation</h4>
                <div class="calculation-steps">
                    <div class="calc-step">
                        <div class="step-num">1</div>
                        <div class="step-content">
                            <div class="step-label">Collect Data</div>
                            <div class="step-value">${bellData.totalPoints} years of ${isHigh ? 'maximum' : 'minimum'} temperatures</div>
                            <div class="step-data">${bellData.dataPoints.map(p => p.value + '°C').join(', ')}</div>
                        </div>
                    </div>
                    <div class="calc-step">
                        <div class="step-num">2</div>
                        <div class="step-content">
                            <div class="step-label">Calculate Mean (μ)</div>
                            <div class="step-formula">μ = Σx / n = ${bellData.dataPoints.map(p => p.value).reduce((a,b) => a+b, 0).toFixed(1)} / ${bellData.totalPoints}</div>
                            <div class="step-result"><strong>μ = ${bellData.mean.toFixed(2)}°C</strong></div>
                        </div>
                    </div>
                    <div class="calc-step">
                        <div class="step-num">3</div>
                        <div class="step-content">
                            <div class="step-label">Calculate Standard Deviation (σ)</div>
                            <div class="step-formula">σ = √[Σ(x - μ)² / (n-1)]</div>
                            <div class="step-result"><strong>σ = ${bellData.std.toFixed(2)}°C</strong></div>
                        </div>
                    </div>
                    <div class="calc-step highlight">
                        <div class="step-num">4</div>
                        <div class="step-content">
                            <div class="step-label">Apply 98% Reliability</div>
                            <div class="step-formula">T<sub>98%</sub> = ${bellData.mean.toFixed(2)} ${isHigh ? '+' : '−'} (2.054 × ${bellData.std.toFixed(2)})</div>
                            <div class="step-formula">T<sub>98%</sub> = ${bellData.mean.toFixed(2)} ${isHigh ? '+' : '−'} ${(2.054 * bellData.std).toFixed(2)}</div>
                            <div class="step-result final"><strong>T<sub>98%</sub> = ${reliability98.toFixed(1)}°C</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="formula-visual-section">
                <h4><i class="fas fa-chart-bell"></i> Normal Distribution Visualization</h4>
                <div class="distribution-visual">
                    <svg viewBox="0 0 400 120" class="bell-mini-svg">
                        <defs>
                            <linearGradient id="bellGrad${chartId}" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:${isHigh ? '#D83B01' : '#0078D4'};stop-opacity:0.6"/>
                                <stop offset="100%" style="stop-color:${isHigh ? '#D83B01' : '#0078D4'};stop-opacity:0.1"/>
                            </linearGradient>
                        </defs>
                        <!-- Bell curve path -->
                        <path d="M 20 100 Q 80 100, 120 80 Q 160 40, 200 20 Q 240 40, 280 80 Q 320 100, 380 100"
                              fill="url(#bellGrad${chartId})" stroke="${isHigh ? '#D83B01' : '#0078D4'}" stroke-width="2"/>
                        <!-- Mean line -->
                        <line x1="200" y1="15" x2="200" y2="105" stroke="#333" stroke-width="2" stroke-dasharray="4"/>
                        <!-- 98% line -->
                        <line x1="${isHigh ? 320 : 80}" y1="15" x2="${isHigh ? 320 : 80}" y2="105" stroke="${isHigh ? '#D83B01' : '#0078D4'}" stroke-width="2"/>
                        <!-- Labels -->
                        <text x="200" y="115" text-anchor="middle" font-size="10" fill="#333">μ = ${bellData.mean.toFixed(1)}°C</text>
                        <text x="${isHigh ? 320 : 80}" y="115" text-anchor="middle" font-size="10" fill="${isHigh ? '#D83B01' : '#0078D4'}" font-weight="bold">98% = ${reliability98.toFixed(1)}°C</text>
                        <!-- Shaded area annotation -->
                        <text x="${isHigh ? 350 : 50}" y="60" text-anchor="middle" font-size="9" fill="${isHigh ? '#D83B01' : '#0078D4'}">2%</text>
                    </svg>
                    <div class="distribution-legend">
                        <span><span class="legend-dot mean"></span> Mean (μ)</span>
                        <span><span class="legend-dot p98" style="background:${isHigh ? '#D83B01' : '#0078D4'}"></span> 98% Reliability</span>
                    </div>
                </div>
            </div>

            <div class="formula-visual-section">
                <h4><i class="fas fa-road"></i> Engineering Application</h4>
                <div class="engineering-result">
                    <div class="eng-result-header">
                        <span class="eng-label">Recommended PG ${isHigh ? 'High' : 'Low'} Grade:</span>
                        <span class="eng-value ${isHigh ? 'high' : 'low'}">${isHigh ? Math.ceil(reliability98 / 6) * 6 : Math.floor(reliability98 / 6) * 6}°C</span>
                    </div>
                    <div class="eng-explanation">
                        <p>${isHigh
                            ? `Based on 98% reliability high temperature of <strong>${reliability98.toFixed(1)}°C</strong>, select PG binder with high-temperature grade ≥ <strong>${Math.ceil(reliability98 / 6) * 6}</strong>.`
                            : `Based on 98% reliability low temperature of <strong>${reliability98.toFixed(1)}°C</strong>, select PG binder with low-temperature grade ≤ <strong>${Math.floor(reliability98 / 6) * 6}</strong>.`
                        }</p>
                        <p class="eng-note">PG grades are specified in 6°C increments (e.g., PG 64-22, PG 70-28).</p>
                    </div>
                </div>
            </div>

            <div class="formula-source-box">
                <div class="source-icon"><i class="fas fa-book"></i></div>
                <div class="source-content">
                    <div class="source-title">Reference</div>
                    <div class="source-text">SHRP-A-648A: "Background of SUPERPAVE Asphalt Binder Test Methods"<br>
                    Strategic Highway Research Program, National Research Council, 1994</div>
                </div>
            </div>`;
    }

    // Show popup
    showAnalyticsPopup(title, content);
}

function calculatePercentile(sigma) {
    // Approximation of cumulative normal distribution
    const t = 1 / (1 + 0.2316419 * Math.abs(sigma));
    const d = 0.3989423 * Math.exp(-sigma * sigma / 2);
    const p = d * t * (0.3193815 + t * (-0.3565638 + t * (1.781478 + t * (-1.821256 + t * 1.330274))));
    return sigma > 0 ? ((1 - p) * 100).toFixed(1) : (p * 100).toFixed(1);
}

function getRank(value, dataPoints) {
    const sorted = [...dataPoints].sort((a, b) => b.value - a.value);
    return sorted.findIndex(p => p.value === value) + 1;
}

function getEngineeringImplication(point, bellData) {
    const isHigh = bellData.type === 'high';
    const sigma = Math.abs(point.sigma);

    if (sigma > 2) {
        return isHigh
            ? `⚠️ This year had exceptionally high temperatures. Consider using polymer-modified binder (PG XX-YY + modifier) for projects designed based on this period. Increased rutting risk.`
            : `⚠️ This year had exceptionally low temperatures. Consider softer grade binder to prevent thermal cracking. Increased cracking risk.`;
    } else if (sigma > 1) {
        return isHigh
            ? `This year was warmer than average. Standard high-grade binder should be adequate but monitor for rutting on heavy traffic routes.`
            : `This year was colder than average. Ensure binder low-temperature grade provides adequate margin.`;
    }
    return `This year was within normal range. Standard PG grade selection based on mean values is appropriate.`;
}

function showAnalyticsPopup(title, content) {
    // Remove existing popup
    const existing = document.getElementById('bellAnalyticsPopup');
    if (existing) existing.remove();

    const popup = document.createElement('div');
    popup.id = 'bellAnalyticsPopup';
    popup.className = 'bell-analytics-popup';
    popup.innerHTML = `
        <div class="bell-analytics-overlay" onclick="closeBellAnalytics()"></div>
        <div class="bell-analytics-content">
            <div class="bell-analytics-header">
                <h3>${title}</h3>
                <button onclick="closeBellAnalytics()" class="close-btn">&times;</button>
            </div>
            <div class="bell-analytics-body">${content}</div>
        </div>
    `;
    document.body.appendChild(popup);
    setTimeout(() => popup.classList.add('show'), 10);
}

function closeBellAnalytics() {
    const popup = document.getElementById('bellAnalyticsPopup');
    if (popup) {
        popup.classList.remove('show');
        setTimeout(() => popup.remove(), 300);
    }
}

function renderPavementFormulaExplanation(data) {
    const lat = data.lat;
    const avgHigh = data.avg_high;
    const avgLow = data.avg_low;
    const stdHigh = data.std_high;
    const stdLow = data.std_low;
    const hotDays = $('#pavementHotDays').val() || 7;
    const coldDays = $('#pavementColdDays').val() || 1;

    // Calculate intermediate values
    const latSq = (lat * lat).toFixed(4);
    const term1 = (0.00618 * lat * lat).toFixed(4);
    const term2 = (0.2289 * lat).toFixed(4);

    // 50% Reliability
    const maxAir50 = avgHigh;
    const minAir50 = avgLow;
    const inner50 = (maxAir50 - 0.00618 * lat * lat + 0.2289 * lat + 42.2).toFixed(2);
    const maxPvt50 = ((maxAir50 - 0.00618 * lat * lat + 0.2289 * lat + 42.2) * 0.9545 - 17.78).toFixed(1);

    // 98% Reliability
    const maxAir98 = (avgHigh + 2.055 * stdHigh).toFixed(1);
    const minAir98 = (avgLow - 2.055 * stdLow).toFixed(1);
    const inner98 = (parseFloat(maxAir98) - 0.00618 * lat * lat + 0.2289 * lat + 42.2).toFixed(2);
    const maxPvt98 = ((parseFloat(maxAir98) - 0.00618 * lat * lat + 0.2289 * lat + 42.2) * 0.9545 - 17.78).toFixed(1);

    const html = `
        <!-- Input Data Section -->
        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon input"><i class="ri-database-2-line"></i></div>
                <h4>Input Data & Statistics</h4>
            </div>
            <div class="formula-section-body">
                <div class="formula-grid">
                    <div class="formula-item highlight">
                        <span class="item-label">Latitude</span>
                        <span class="item-value">${lat}°</span>
                    </div>
                    <div class="formula-item">
                        <span class="item-label">Years of Data</span>
                        <span class="item-value">${data.yearly_data ? data.yearly_data.length : '-'} years</span>
                    </div>
                    <div class="formula-item high">
                        <span class="item-label">Average High (${hotDays}-day)</span>
                        <span class="item-value">${avgHigh}°C</span>
                    </div>
                    <div class="formula-item high">
                        <span class="item-label">Std Dev (High)</span>
                        <span class="item-value">σ = ${stdHigh}°C</span>
                    </div>
                    <div class="formula-item low">
                        <span class="item-label">Average Low (${coldDays}-day)</span>
                        <span class="item-value">${avgLow}°C</span>
                    </div>
                    <div class="formula-item low">
                        <span class="item-label">Std Dev (Low)</span>
                        <span class="item-value">σ = ${stdLow}°C</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 50% Reliability Calculation -->
        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon calc"><i class="ri-calculator-line"></i></div>
                <h4>50% Reliability Calculation</h4>
            </div>
            <div class="formula-section-body">
                <p style="margin: 0 0 16px; color: var(--fluent-text-secondary); font-size: 13px;">
                    At 50% reliability, we use the average values directly without standard deviation adjustment.
                </p>

                <div class="formula-grid" style="margin-bottom: 16px;">
                    <div class="formula-item high">
                        <span class="item-label">MAX AIR (50%)</span>
                        <span class="item-value">${maxAir50}°C</span>
                    </div>
                    <div class="formula-item low">
                        <span class="item-label">MIN AIR (50%)</span>
                        <span class="item-value">${minAir50}°C</span>
                    </div>
                </div>

                <div class="formula-box">
                    <div class="formula-title">SUPERPAVE Pavement Temperature Formula (20mm depth)</div>
                    <div class="formula-text">
                        T<sub>20mm</sub> = (T<sub>air</sub> - 0.00618 × lat² + 0.2289 × lat + 42.2) × 0.9545 - 17.78
                    </div>
                </div>

                <div class="calculation-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <div class="step-label">Calculate lat² term</div>
                        <div class="step-formula">lat² = ${lat}² = ${latSq}</div>
                    </div>
                </div>

                <div class="calculation-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <div class="step-label">Calculate inner expression</div>
                        <div class="step-formula">${maxAir50} - (0.00618 × ${latSq}) + (0.2289 × ${lat}) + 42.2</div>
                        <div class="step-formula">${maxAir50} - ${term1} + ${term2} + 42.2 = ${inner50}</div>
                    </div>
                </div>

                <div class="calculation-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <div class="step-label">Apply depth correction factor</div>
                        <div class="step-formula">${inner50} × 0.9545 - 17.78 = ${maxPvt50}°C</div>
                        <div class="step-result">MAX PVT (50%) = ${data.max_pvt_50}°C</div>
                    </div>
                </div>

                <div class="calculation-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <div class="step-label">MIN PVT equals MIN AIR (surface temperature)</div>
                        <div class="step-result">MIN PVT (50%) = ${data.min_pvt_50}°C</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 98% Reliability Calculation -->
        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon result"><i class="ri-shield-check-line"></i></div>
                <h4>98% Reliability Calculation</h4>
            </div>
            <div class="formula-section-body">
                <p style="margin: 0 0 16px; color: var(--fluent-text-secondary); font-size: 13px;">
                    At 98% reliability, we adjust by ±2.055 standard deviations to account for extreme conditions.
                </p>

                <div class="formula-box">
                    <div class="formula-title">98% Reliability Adjustment</div>
                    <div class="formula-text">
                        MAX AIR (98%) = AVG HIGH + 2.055 × σ<sub>high</sub> = ${avgHigh} + 2.055 × ${stdHigh} = ${maxAir98}°C<br>
                        MIN AIR (98%) = AVG LOW - 2.055 × σ<sub>low</sub> = ${avgLow} - 2.055 × ${stdLow} = ${minAir98}°C
                    </div>
                </div>

                <div class="formula-grid" style="margin-bottom: 16px;">
                    <div class="formula-item high">
                        <span class="item-label">MAX AIR (98%)</span>
                        <span class="item-value">${data.max_air_98}°C</span>
                    </div>
                    <div class="formula-item pvt">
                        <span class="item-label">MAX PVT (98%)</span>
                        <span class="item-value">${data.max_pvt_98}°C</span>
                    </div>
                    <div class="formula-item low">
                        <span class="item-label">MIN AIR (98%)</span>
                        <span class="item-value">${data.min_air_98}°C</span>
                    </div>
                    <div class="formula-item pvt">
                        <span class="item-label">MIN PVT (98%)</span>
                        <span class="item-value">${data.min_pvt_98}°C</span>
                    </div>
                </div>

                <div class="verification-badge">
                    <i class="ri-checkbox-circle-fill"></i>
                    Calculation verified using SUPERPAVE methodology (SHRP-A-648A)
                </div>
            </div>
        </div>
    `;

    $('#pavementFormulaExplanation').html(html);
}

function renderInlinePavementChart(data) {
    // Remove existing inline chart section if any
    $('#inlineChartSection').remove();

    const avgHigh = data.avg_high;
    const avgLow = data.avg_low;
    const stdHigh = data.std_high || 1;
    const stdLow = data.std_low || 1;

    // Add inline chart section after table
    const inlineHtml = `
        <div id="inlineChartSection" class="inline-chart-section">
            <div class="inline-chart-header">
                <h4><i class="ri-line-chart-line"></i> ${data.station_name} - Normal Distribution</h4>
                <div class="chart-legend-inline">
                    <span class="legend-item"><span class="dot high"></span> High Temp (μ=${avgHigh}°C, σ=${stdHigh}°C)</span>
                    <span class="legend-item"><span class="dot low"></span> Low Temp (μ=${avgLow}°C, σ=${stdLow}°C)</span>
                </div>
            </div>
            <div class="inline-chart-body">
                <div class="bell-curve-grid" style="margin-bottom: 0;">
                    <div class="bell-curve-card high" style="border: none;">
                        <div class="bell-curve-wrapper" style="height: 200px;">
                            <canvas id="inlineHighBellCurve"></canvas>
                        </div>
                        <div class="bell-curve-stats" id="inlineHighStats"></div>
                    </div>
                    <div class="bell-curve-card low" style="border: none;">
                        <div class="bell-curve-wrapper" style="height: 200px;">
                            <canvas id="inlineLowBellCurve"></canvas>
                        </div>
                        <div class="bell-curve-stats" id="inlineLowStats"></div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('.analysis-table-card.statistics-section').after(inlineHtml);

    // Render bell curves
    const yearlyData = data.yearly_data || [];

    // Render High Temperature Bell Curve
    renderInlineBellCurve('inlineHighBellCurve', avgHigh, stdHigh, yearlyData.map(d => d.high_avg), '#D83B01');

    // Render Low Temperature Bell Curve
    renderInlineBellCurve('inlineLowBellCurve', avgLow, stdLow, yearlyData.map(d => d.low_avg), '#0078D4');

    // Update stats
    $('#inlineHighStats').html(`
        <div class="bell-stat-item">
            <div class="stat-label">Mean (μ)</div>
            <div class="stat-value" style="color: #D83B01;">${avgHigh}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">Std Dev (σ)</div>
            <div class="stat-value" style="color: #D83B01;">±${stdHigh}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">98% Max</div>
            <div class="stat-value" style="color: #D83B01;">${(avgHigh + 2*stdHigh).toFixed(1)}°C</div>
        </div>
    `);

    $('#inlineLowStats').html(`
        <div class="bell-stat-item">
            <div class="stat-label">Mean (μ)</div>
            <div class="stat-value" style="color: #0078D4;">${avgLow}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">Std Dev (σ)</div>
            <div class="stat-value" style="color: #0078D4;">±${stdLow}°C</div>
        </div>
        <div class="bell-stat-item">
            <div class="stat-label">98% Min</div>
            <div class="stat-value" style="color: #0078D4;">${(avgLow - 2*stdLow).toFixed(1)}°C</div>
        </div>
    `);
}

function renderInlineBellCurve(canvasId, mean, std, dataPoints, color) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    // Generate bell curve data
    const numPoints = 80;
    const range = std * 3.5;
    const minX = mean - range;
    const maxX = mean + range;
    const step = (maxX - minX) / numPoints;

    const labels = [];
    const bellData = [];

    const gaussian = (x, mean, std) => {
        return (1 / (std * Math.sqrt(2 * Math.PI))) * Math.exp(-0.5 * Math.pow((x - mean) / std, 2));
    };

    for (let i = 0; i <= numPoints; i++) {
        const x = minX + (i * step);
        labels.push(x.toFixed(1));
        bellData.push(gaussian(x, mean, std));
    }

    const maxBell = Math.max(...bellData);
    const normalizedBell = bellData.map(v => (v / maxBell) * 100);

    const datasets = [];

    // ±2σ region
    const band2Data = labels.map((x, i) => {
        const xVal = parseFloat(x);
        return (xVal >= mean - 2*std && xVal <= mean + 2*std) ? normalizedBell[i] : null;
    });
    datasets.push({
        data: band2Data,
        borderColor: 'transparent',
        backgroundColor: color.replace(')', ', 0.25)').replace('rgb', 'rgba'),
        fill: true,
        pointRadius: 0,
        tension: 0.4
    });

    // ±1σ region
    const band1Data = labels.map((x, i) => {
        const xVal = parseFloat(x);
        return (xVal >= mean - std && xVal <= mean + std) ? normalizedBell[i] : null;
    });
    datasets.push({
        data: band1Data,
        borderColor: 'transparent',
        backgroundColor: color.replace(')', ', 0.45)').replace('rgb', 'rgba'),
        fill: true,
        pointRadius: 0,
        tension: 0.4
    });

    // Main curve
    datasets.push({
        data: normalizedBell,
        borderColor: color,
        backgroundColor: 'transparent',
        borderWidth: 2,
        fill: false,
        pointRadius: 0,
        tension: 0.4
    });

    new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false, min: 0, max: 110 },
                x: {
                    ticks: {
                        callback: function(value) {
                            const label = parseFloat(this.getLabelForValue(value));
                            if (Math.abs(label - mean) < 0.3) return `μ`;
                            if (Math.abs(label - (mean - std)) < 0.3) return `-1σ`;
                            if (Math.abs(label - (mean + std)) < 0.3) return `+1σ`;
                            if (Math.abs(label - (mean - 2*std)) < 0.3) return `-2σ`;
                            if (Math.abs(label - (mean + 2*std)) < 0.3) return `+2σ`;
                            return '';
                        },
                        maxRotation: 0,
                        font: { size: 9 }
                    },
                    grid: { display: false }
                }
            }
        }
    });
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


// View station details
function viewStationDetails(stationId) {
    window.location.href = '/admin/stations?view=' + stationId;
}
</script>
@endsection
