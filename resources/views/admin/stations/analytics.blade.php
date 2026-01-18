@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <div class="page-header-left">
        <a href="{{ route('admin.stations.index') }}" class="back-btn">
            <i class="ri-arrow-left-line"></i>
        </a>
        <div>
            <h1 class="fluent-page-title">
                <span class="fluent-page-title-icon">
                    <i class="ri-bar-chart-box-line"></i>
                </span>
                {{ $station->station_name }} - Weather Analytics
            </h1>
            <p class="page-subtitle">
                <i class="ri-calendar-line"></i>
                Data from {{ $dateRange->min_date ? \Carbon\Carbon::parse($dateRange->min_date)->format('M Y') : 'N/A' }}
                to {{ $dateRange->max_date ? \Carbon\Carbon::parse($dateRange->max_date)->format('M Y') : 'N/A' }}
            </p>
        </div>
    </div>
</div>

<!-- View Toggle & Controls -->
<div class="analytics-controls">
    <div class="view-toggle">
        <button type="button" class="view-btn active" data-view="month">
            <i class="ri-calendar-event-line"></i>
            Month View
        </button>
        <button type="button" class="view-btn" data-view="year">
            <i class="ri-calendar-2-line"></i>
            Year View
        </button>
        <button type="button" class="view-btn" data-view="decade">
            <i class="ri-calendar-todo-line"></i>
            Decade View
        </button>
    </div>

    <div class="date-controls">
        <!-- Month View Controls -->
        <div class="control-group" id="monthControls">
            <select id="selectYear" class="fluent-select">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <select id="selectMonth" class="fluent-select">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
        </div>

        <!-- Year View Controls -->
        <div class="control-group hidden" id="yearControls">
            <select id="selectYearOnly" class="fluent-select">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <!-- Decade View Controls -->
        <div class="control-group hidden" id="decadeControls">
            <select id="selectDecade" class="fluent-select">
                @php
                    $minYear = !empty($availableYears) ? min($availableYears) : date('Y') - 9;
                    $maxYear = !empty($availableYears) ? max($availableYears) : date('Y');
                    // Calculate decade start points
                    $decadeStart = floor($maxYear / 10) * 10;
                    $decadeEnd = floor($minYear / 10) * 10;
                @endphp
                @for($y = $decadeStart; $y >= $decadeEnd; $y -= 10)
                    <option value="{{ $y }}">{{ $y }} - {{ $y + 9 }}</option>
                @endfor
                @if($decadeStart == $decadeEnd)
                    {{-- Ensure at least one option exists --}}
                @endif
            </select>
        </div>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="loading-overlay">
    <div class="loading-content">
        <div class="loading-spinner">
            <i class="ri-loader-4-line"></i>
        </div>
        <p>Loading weather data...</p>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-row" id="summaryStats">
    <!-- Filled dynamically -->
</div>

<!-- Charts Section -->
<div class="charts-container">
    <!-- Temperature Chart -->
    <div class="chart-card full-width">
        <div class="chart-header">
            <div class="chart-title">
                <i class="ri-temp-hot-line"></i>
                <span>Temperature Overview</span>
            </div>
            <div class="chart-legend" id="tempLegend"></div>
        </div>
        <div class="chart-body">
            <canvas id="temperatureChart"></canvas>
        </div>
    </div>

    <!-- Rainfall Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                <i class="ri-rainy-line"></i>
                <span>Rainfall</span>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="rainfallChart"></canvas>
        </div>
    </div>

    <!-- Sunshine Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                <i class="ri-sun-line"></i>
                <span>Sunshine Hours</span>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="sunshineChart"></canvas>
        </div>
    </div>

    <!-- Humidity Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                <i class="ri-drop-line"></i>
                <span>Humidity</span>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="humidityChart"></canvas>
        </div>
    </div>

    <!-- Combined Chart (for trends) -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                <i class="ri-line-chart-line"></i>
                <span id="combinedChartTitle">Temperature & Rainfall Comparison</span>
            </div>
        </div>
        <div class="chart-body">
            <canvas id="combinedChart"></canvas>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="data-table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="ri-table-line"></i>
            <span>Detailed Data</span>
        </div>
        <button class="fluent-btn fluent-btn-secondary" onclick="exportToCSV()">
            <i class="ri-download-line"></i>
            Export CSV
        </button>
    </div>
    <div class="table-body">
        <table class="fluent-table" id="dataTable">
            <thead id="tableHead"></thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Page Header */
.page-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--fluent-bg-primary);
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    color: var(--fluent-text-secondary);
    text-decoration: none;
    transition: all 0.2s ease;
}
.back-btn:hover {
    background: var(--fluent-primary);
    border-color: var(--fluent-primary);
    color: white;
}
.page-subtitle {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--fluent-text-secondary);
    margin-top: 4px;
}

/* Analytics Controls */
.analytics-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
    padding: 16px 20px;
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
}
.view-toggle {
    display: flex;
    background: var(--fluent-gray-20);
    border-radius: var(--fluent-radius-md);
    padding: 4px;
}
.view-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    background: transparent;
    border-radius: var(--fluent-radius-sm);
    color: var(--fluent-text-secondary);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}
.view-btn:hover {
    color: var(--fluent-text-primary);
}
.view-btn.active {
    background: var(--fluent-primary);
    color: white;
    box-shadow: var(--fluent-shadow-4);
}
.date-controls {
    display: flex;
    gap: 12px;
}
.control-group {
    display: flex;
    gap: 8px;
}
.control-group.hidden {
    display: none;
}
.fluent-select {
    padding: 10px 14px;
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    font-size: 14px;
    min-width: 120px;
    cursor: pointer;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}
.loading-overlay.active {
    opacity: 1;
    visibility: visible;
}
.loading-spinner i {
    font-size: 48px;
    color: var(--fluent-primary);
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--fluent-bg-primary);
    padding: 20px;
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--fluent-radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.stat-icon.temp-high { background: linear-gradient(135deg, #FF6B6B 0%, #EE5A24 100%); color: white; }
.stat-icon.temp-low { background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); color: white; }
.stat-icon.rain { background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%); color: white; }
.stat-icon.humidity { background: linear-gradient(135deg, #81ecec 0%, #00cec9 100%); color: white; }
.stat-icon.sun { background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); color: #333; }
.stat-icon.info { background: linear-gradient(135deg, #dfe6e9 0%, #b2bec3 100%); color: #333; }
.stat-content {
    flex: 1;
}
.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--fluent-text-primary);
    line-height: 1.2;
}
.stat-label {
    font-size: 12px;
    color: var(--fluent-text-secondary);
    margin-top: 2px;
}
.stat-trend {
    font-size: 11px;
    font-weight: 600;
    margin-top: 4px;
}
.stat-trend.up { color: #e74c3c; }
.stat-trend.down { color: #27ae60; }

/* Charts Container */
.charts-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}
.chart-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
}
.chart-card.full-width {
    grid-column: 1 / -1;
}
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--fluent-gray-20);
    background: var(--fluent-gray-10);
}
.chart-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--fluent-text-primary);
}
.chart-title i {
    font-size: 18px;
    color: var(--fluent-primary);
}
.chart-legend {
    display: flex;
    gap: 16px;
    font-size: 12px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}
.chart-body {
    padding: 20px;
    height: 300px;
    position: relative;
}
.chart-card.full-width .chart-body {
    height: 350px;
}

/* Data Table */
.data-table-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
}
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--fluent-gray-20);
    background: var(--fluent-gray-10);
}
.table-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--fluent-text-primary);
}
.table-title i {
    font-size: 18px;
    color: var(--fluent-primary);
}
.table-body {
    max-height: 400px;
    overflow-y: auto;
}
.fluent-table {
    width: 100%;
    border-collapse: collapse;
}
.fluent-table th {
    position: sticky;
    top: 0;
    background: var(--fluent-gray-20);
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fluent-text-secondary);
    border-bottom: 1px solid var(--fluent-gray-30);
}
.fluent-table td {
    padding: 12px 16px;
    font-size: 14px;
    color: var(--fluent-text-primary);
    border-bottom: 1px solid var(--fluent-gray-20);
}
.fluent-table tr:hover td {
    background: var(--fluent-gray-10);
}

/* Responsive */
@media (max-width: 992px) {
    .analytics-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .control-group {
        width: 100%;
    }
    .charts-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .fluent-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .fluent-page-actions {
        width: 100%;
    }
    .fluent-page-actions .fluent-btn {
        flex: 1;
        justify-content: center;
    }
    .analytics-controls {
        flex-direction: column;
        align-items: stretch;
        padding: 16px;
    }
    .view-toggle {
        overflow-x: auto;
        width: 100%;
    }
    .view-btn {
        flex: 1;
        min-width: 80px;
        padding: 10px 12px;
        font-size: 13px;
    }
    .date-controls {
        width: 100%;
    }
    .date-controls .fluent-select {
        width: 100%;
    }
    .charts-container {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .chart-card {
        min-height: 280px;
    }
    .chart-card.full-width {
        grid-column: 1;
    }
    .chart-header {
        padding: 12px 16px;
    }
    .chart-header h4 {
        font-size: 14px;
    }
    .chart-body {
        padding: 12px;
    }
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .stat-card {
        padding: 16px;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    .stat-value {
        font-size: 22px;
    }
    .stat-label {
        font-size: 12px;
    }
    /* Table */
    .fluent-table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .fluent-table {
        min-width: 600px;
    }
    .fluent-table th,
    .fluent-table td {
        padding: 10px 12px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .fluent-page-title {
        font-size: 18px;
    }
    .fluent-page-subtitle {
        font-size: 12px;
    }
    .stats-row {
        grid-template-columns: 1fr;
    }
    .view-btn {
        font-size: 12px;
        padding: 8px 10px;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart instances
let temperatureChart, rainfallChart, sunshineChart, humidityChart, combinedChart;

// Current view state
let currentView = 'month';
let currentData = [];

// Chart colors
const colors = {
    maxTemp: { bg: 'rgba(255, 107, 107, 0.2)', border: '#FF6B6B' },
    minTemp: { bg: 'rgba(116, 185, 255, 0.2)', border: '#74b9ff' },
    avgTemp: { bg: 'rgba(253, 203, 110, 0.2)', border: '#fdcb6e' },
    rainfall: { bg: 'rgba(162, 155, 254, 0.6)', border: '#6c5ce7' },
    sunshine: { bg: 'rgba(253, 203, 110, 0.6)', border: '#f9ca24' },
    humidity: { bg: 'rgba(129, 236, 236, 0.6)', border: '#00cec9' },
};

// Initialize
$(function() {
    initCharts();
    loadData();

    // View toggle
    $('.view-btn').on('click', function() {
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        currentView = $(this).data('view');
        updateControlsVisibility();
        loadData();
    });

    // Date controls
    $('#selectYear, #selectMonth').on('change', function() {
        if (currentView === 'month') loadData();
    });
    $('#selectYearOnly').on('change', function() {
        if (currentView === 'year') loadData();
    });
    $('#selectDecade').on('change', function() {
        if (currentView === 'decade') loadData();
    });
});

function updateControlsVisibility() {
    $('#monthControls, #yearControls, #decadeControls').addClass('hidden');
    if (currentView === 'month') $('#monthControls').removeClass('hidden');
    else if (currentView === 'year') $('#yearControls').removeClass('hidden');
    else $('#decadeControls').removeClass('hidden');
}

function initCharts() {
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleFont: { size: 13 },
                bodyFont: { size: 12 },
                padding: 12,
                cornerRadius: 8,
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            },
            y: {
                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                ticks: { font: { size: 11 } }
            }
        }
    };

    // Temperature Chart (Line)
    temperatureChart = new Chart(document.getElementById('temperatureChart'), {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
            ...defaultOptions,
            plugins: {
                ...defaultOptions.plugins,
                legend: { display: true, position: 'top' }
            }
        }
    });

    // Rainfall Chart (Bar)
    rainfallChart = new Chart(document.getElementById('rainfallChart'), {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: defaultOptions
    });

    // Sunshine Chart (Area)
    sunshineChart = new Chart(document.getElementById('sunshineChart'), {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
            ...defaultOptions,
            elements: { line: { fill: true } }
        }
    });

    // Humidity Chart (Line with gradient)
    humidityChart = new Chart(document.getElementById('humidityChart'), {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
            ...defaultOptions,
            elements: { line: { fill: true } }
        }
    });

    // Combined Chart (Mixed)
    combinedChart = new Chart(document.getElementById('combinedChart'), {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
            ...defaultOptions,
            plugins: {
                ...defaultOptions.plugins,
                legend: { display: true, position: 'top' }
            },
            scales: {
                ...defaultOptions.scales,
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
}

function loadData() {
    $('#loadingState').addClass('active');

    let params = { view: currentView };

    if (currentView === 'month') {
        params.year = $('#selectYear').val();
        params.month = $('#selectMonth').val();
    } else if (currentView === 'year') {
        params.year = $('#selectYearOnly').val();
    } else {
        params.decade_start = $('#selectDecade').val();
    }

    $.ajax({
        url: "{{ route('admin.stations.analyticsData', $station->id) }}",
        data: params,
        success: function(response) {
            $('#loadingState').removeClass('active');
            currentData = response.data;
            updateSummaryStats(response.summary);
            updateCharts(response.data);
            updateTable(response.data);
        },
        error: function() {
            $('#loadingState').removeClass('active');
            alert('Failed to load data');
        }
    });
}

function updateSummaryStats(summary) {
    let html = '';

    // Handle null/undefined summary
    if (!summary) {
        summary = {};
    }

    if (currentView === 'month') {
        html = `
            <div class="stat-card">
                <div class="stat-icon temp-high"><i class="ri-temp-hot-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_max_temp || 0}°C</div>
                    <div class="stat-label">Avg Max Temp</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon temp-low"><i class="ri-temp-cold-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_min_temp || 0}°C</div>
                    <div class="stat-label">Avg Min Temp</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rain"><i class="ri-rainy-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.total_rainfall || 0}mm</div>
                    <div class="stat-label">Total Rainfall</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon humidity"><i class="ri-drop-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_humidity || 0}%</div>
                    <div class="stat-label">Avg Humidity</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sun"><i class="ri-sun-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.total_sunshine || 0}h</div>
                    <div class="stat-label">Total Sunshine</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info"><i class="ri-cloud-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.rainy_days || 0}</div>
                    <div class="stat-label">Rainy Days</div>
                </div>
            </div>
        `;
    } else if (currentView === 'year') {
        html = `
            <div class="stat-card">
                <div class="stat-icon temp-high"><i class="ri-temp-hot-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_max_temp || 0}°C</div>
                    <div class="stat-label">Avg Max Temp</div>
                    <div class="stat-trend">${summary.hottest_month} was hottest</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon temp-low"><i class="ri-temp-cold-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_min_temp || 0}°C</div>
                    <div class="stat-label">Avg Min Temp</div>
                    <div class="stat-trend">${summary.coldest_month} was coldest</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rain"><i class="ri-rainy-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.total_rainfall || 0}mm</div>
                    <div class="stat-label">Total Rainfall</div>
                    <div class="stat-trend">${summary.wettest_month} was wettest</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon humidity"><i class="ri-drop-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_humidity || 0}%</div>
                    <div class="stat-label">Avg Humidity</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sun"><i class="ri-sun-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.total_sunshine || 0}h</div>
                    <div class="stat-label">Total Sunshine</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info"><i class="ri-cloud-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.total_rainy_days || 0}</div>
                    <div class="stat-label">Rainy Days</div>
                </div>
            </div>
        `;
    } else {
        const trendClass = summary.temp_trend > 0 ? 'up' : 'down';
        const trendIcon = summary.temp_trend > 0 ? 'arrow-up' : 'arrow-down';
        html = `
            <div class="stat-card">
                <div class="stat-icon temp-high"><i class="ri-temp-hot-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_max_temp || 0}°C</div>
                    <div class="stat-label">Avg Max Temp</div>
                    <div class="stat-trend ${trendClass}"><i class="ri-${trendIcon}-line"></i> ${Math.abs(summary.temp_trend)}°C trend</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon temp-low"><i class="ri-temp-cold-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_min_temp || 0}°C</div>
                    <div class="stat-label">Avg Min Temp</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rain"><i class="ri-rainy-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${(summary.total_rainfall / 1000).toFixed(1)}k mm</div>
                    <div class="stat-label">Total Rainfall</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon humidity"><i class="ri-drop-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.avg_humidity || 0}%</div>
                    <div class="stat-label">Avg Humidity</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info"><i class="ri-fire-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.hottest_year}</div>
                    <div class="stat-label">Hottest Year</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info"><i class="ri-snowy-line"></i></div>
                <div class="stat-content">
                    <div class="stat-value">${summary.coldest_year}</div>
                    <div class="stat-label">Coldest Year</div>
                </div>
            </div>
        `;
    }

    $('#summaryStats').html(html);
}

function updateCharts(data) {
    if (!data || data.length === 0) {
        // Clear all charts
        [temperatureChart, rainfallChart, sunshineChart, humidityChart, combinedChart].forEach(chart => {
            chart.data.labels = [];
            chart.data.datasets = [];
            chart.update();
        });
        return;
    }

    let labels = [];
    if (currentView === 'month') {
        labels = data.map(d => d.date);
    } else if (currentView === 'year') {
        labels = data.map(d => d.month);
    } else {
        labels = data.map(d => d.year);
    }

    // Temperature Chart
    temperatureChart.data.labels = labels;
    temperatureChart.data.datasets = [
        {
            label: 'Max Temp (°C)',
            data: data.map(d => currentView === 'month' ? d.max_temp : d.avg_max_temp),
            borderColor: colors.maxTemp.border,
            backgroundColor: colors.maxTemp.bg,
            tension: 0.4,
            fill: false,
        },
        {
            label: 'Min Temp (°C)',
            data: data.map(d => currentView === 'month' ? d.min_temp : d.avg_min_temp),
            borderColor: colors.minTemp.border,
            backgroundColor: colors.minTemp.bg,
            tension: 0.4,
            fill: false,
        }
    ];
    temperatureChart.update();

    // Rainfall Chart
    rainfallChart.data.labels = labels;
    rainfallChart.data.datasets = [{
        label: 'Rainfall (mm)',
        data: data.map(d => d.rainfall !== undefined ? d.rainfall : (d.total_rainfall !== undefined ? d.total_rainfall : 0)),
        backgroundColor: colors.rainfall.bg,
        borderColor: colors.rainfall.border,
        borderWidth: 1,
        borderRadius: 4,
    }];
    rainfallChart.update();

    // Sunshine Chart
    sunshineChart.data.labels = labels;
    sunshineChart.data.datasets = [{
        label: 'Sunshine (hours)',
        data: data.map(d => d.sunshine !== undefined ? d.sunshine : (d.total_sunshine !== undefined ? d.total_sunshine : 0)),
        backgroundColor: colors.sunshine.bg,
        borderColor: colors.sunshine.border,
        fill: true,
        tension: 0.4,
    }];
    sunshineChart.update();

    // Humidity Chart
    humidityChart.data.labels = labels;
    humidityChart.data.datasets = [{
        label: 'Humidity (%)',
        data: data.map(d => d.humidity !== undefined ? d.humidity : (d.avg_humidity !== undefined ? d.avg_humidity : 0)),
        backgroundColor: colors.humidity.bg,
        borderColor: colors.humidity.border,
        fill: true,
        tension: 0.4,
    }];
    humidityChart.update();

    // Combined Chart
    $('#combinedChartTitle').text(currentView === 'decade' ? 'Temperature Trend & Rainfall Over Years' : 'Temperature vs Rainfall');
    combinedChart.data.labels = labels;
    combinedChart.data.datasets = [
        {
            type: 'bar',
            label: 'Rainfall (mm)',
            data: data.map(d => d.rainfall !== undefined ? d.rainfall : (d.total_rainfall !== undefined ? d.total_rainfall : 0)),
            backgroundColor: colors.rainfall.bg,
            borderColor: colors.rainfall.border,
            borderWidth: 1,
            yAxisID: 'y',
        },
        {
            type: 'line',
            label: 'Avg Max Temp (°C)',
            data: data.map(d => currentView === 'month' ? (d.max_temp !== undefined ? d.max_temp : 0) : (d.avg_max_temp !== undefined ? d.avg_max_temp : 0)),
            borderColor: colors.maxTemp.border,
            backgroundColor: 'transparent',
            tension: 0.4,
            yAxisID: 'y1',
        }
    ];
    combinedChart.update();
}

function updateTable(data) {
    if (!data || data.length === 0) {
        $('#tableHead').html('<tr><th>No data available</th></tr>');
        $('#tableBody').html('');
        return;
    }

    let headHtml = '<tr>';
    let bodyHtml = '';

    if (currentView === 'month') {
        headHtml += '<th>Date</th><th>Max Temp</th><th>Min Temp</th><th>Humidity</th><th>Rainfall</th><th>Sunshine</th>';
        data.forEach(d => {
            bodyHtml += `<tr>
                <td>${d.full_date}</td>
                <td>${d.max_temp ? d.max_temp + '°C' : '-'}</td>
                <td>${d.min_temp ? d.min_temp + '°C' : '-'}</td>
                <td>${d.humidity ? d.humidity + '%' : '-'}</td>
                <td>${d.rainfall ? d.rainfall + 'mm' : '-'}</td>
                <td>${d.sunshine ? d.sunshine + 'h' : '-'}</td>
            </tr>`;
        });
    } else if (currentView === 'year') {
        headHtml += '<th>Month</th><th>Avg Max</th><th>Avg Min</th><th>Peak</th><th>Low</th><th>Humidity</th><th>Rainfall</th><th>Rainy Days</th>';
        data.forEach(d => {
            bodyHtml += `<tr>
                <td>${d.month}</td>
                <td>${d.avg_max_temp ? d.avg_max_temp + '°C' : '-'}</td>
                <td>${d.avg_min_temp ? d.avg_min_temp + '°C' : '-'}</td>
                <td>${d.max_temp_peak ? d.max_temp_peak + '°C' : '-'}</td>
                <td>${d.min_temp_low ? d.min_temp_low + '°C' : '-'}</td>
                <td>${d.avg_humidity ? d.avg_humidity + '%' : '-'}</td>
                <td>${d.total_rainfall ? d.total_rainfall + 'mm' : '-'}</td>
                <td>${d.rainy_days || 0}</td>
            </tr>`;
        });
    } else {
        headHtml += '<th>Year</th><th>Avg Max</th><th>Avg Min</th><th>Peak</th><th>Low</th><th>Humidity</th><th>Rainfall</th><th>Rainy Days</th>';
        data.forEach(d => {
            bodyHtml += `<tr>
                <td>${d.year}</td>
                <td>${d.avg_max_temp ? d.avg_max_temp + '°C' : '-'}</td>
                <td>${d.avg_min_temp ? d.avg_min_temp + '°C' : '-'}</td>
                <td>${d.max_temp_peak ? d.max_temp_peak + '°C' : '-'}</td>
                <td>${d.min_temp_low ? d.min_temp_low + '°C' : '-'}</td>
                <td>${d.avg_humidity ? d.avg_humidity + '%' : '-'}</td>
                <td>${d.total_rainfall ? d.total_rainfall + 'mm' : '-'}</td>
                <td>${d.rainy_days || 0}</td>
            </tr>`;
        });
    }

    headHtml += '</tr>';
    $('#tableHead').html(headHtml);
    $('#tableBody').html(bodyHtml);
}

function exportToCSV() {
    if (!currentData || currentData.length === 0) {
        alert('No data to export');
        return;
    }

    let csv = '';
    let headers = [];

    if (currentView === 'month') {
        headers = ['Date', 'Max Temp (°C)', 'Min Temp (°C)', 'Humidity (%)', 'Rainfall (mm)', 'Sunshine (h)'];
        csv = headers.join(',') + '\n';
        currentData.forEach(d => {
            csv += `${d.full_date},${d.max_temp || ''},${d.min_temp || ''},${d.humidity || ''},${d.rainfall || ''},${d.sunshine || ''}\n`;
        });
    } else if (currentView === 'year') {
        headers = ['Month', 'Avg Max (°C)', 'Avg Min (°C)', 'Peak (°C)', 'Low (°C)', 'Humidity (%)', 'Rainfall (mm)', 'Rainy Days'];
        csv = headers.join(',') + '\n';
        currentData.forEach(d => {
            csv += `${d.month},${d.avg_max_temp || ''},${d.avg_min_temp || ''},${d.max_temp_peak || ''},${d.min_temp_low || ''},${d.avg_humidity || ''},${d.total_rainfall || ''},${d.rainy_days || ''}\n`;
        });
    } else {
        headers = ['Year', 'Avg Max (°C)', 'Avg Min (°C)', 'Peak (°C)', 'Low (°C)', 'Humidity (%)', 'Rainfall (mm)', 'Rainy Days'];
        csv = headers.join(',') + '\n';
        currentData.forEach(d => {
            csv += `${d.year},${d.avg_max_temp || ''},${d.avg_min_temp || ''},${d.max_temp_peak || ''},${d.min_temp_low || ''},${d.avg_humidity || ''},${d.total_rainfall || ''},${d.rainy_days || ''}\n`;
        });
    }

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `weather_data_{{ $station->station_name }}_${currentView}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endsection
