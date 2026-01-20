@extends('layouts.admin')
@section('title', 'Construction Weather Analysis - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon" style="background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);">
            <i class="ri-building-2-line"></i>
        </span>
        Construction Weather Analysis
    </h1>
    <div class="fluent-page-actions">
        <a href="{{ route('admin.daily-weathers.index') }}" class="fluent-btn fluent-btn-ghost">
            <i class="ri-arrow-left-line"></i> Back to Weather Dashboard
        </a>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
        <div class="fluent-loader-spinner">
            <div class="fluent-loader-ring"></div>
            <div class="fluent-loader-ring"></div>
            <div class="fluent-loader-ring"></div>
        </div>
        <p>Loading weather data...</p>
    </div>
</div>

<!-- Filter Card -->
<div class="analysis-filter-card">
    <div class="analysis-filter-header">
        <div class="analysis-filter-title">
            <div class="analysis-title-icon" style="background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);">
                <i class="ri-building-2-line"></i>
            </div>
            <div>
                <h2>Construction Weather Analysis</h2>
                <p>Multi-year statistical analysis for construction planning in Bangladesh</p>
            </div>
        </div>
        <button type="button" id="runConstructionAnalysis" class="fluent-btn fluent-btn-primary fluent-btn-lg">
            <i class="ri-play-circle-line"></i> Run Analysis
        </button>
    </div>

    <div class="analysis-filter-body">
        <!-- Parameters Grid -->
        <div class="params-grid params-grid-4">
            <div class="param-card param-card-wide">
                <div class="param-icon station"><i class="ri-map-pin-line"></i></div>
                <div class="param-content">
                    <label>Select Stations</label>
                    <div id="constructionStationSelect" class="multiselect-container"></div>
                </div>
            </div>
            <div class="param-card">
                <div class="param-icon param-date"><i class="ri-calendar-event-line"></i></div>
                <div class="param-content">
                    <label>From Date</label>
                    <input type="date" id="constructionFromDate" class="fluent-input" value="{{ $dateRange->min_date ?? '2015-01-01' }}">
                </div>
            </div>
            <div class="param-card">
                <div class="param-icon param-date"><i class="ri-calendar-check-line"></i></div>
                <div class="param-content">
                    <label>To Date</label>
                    <input type="date" id="constructionToDate" class="fluent-input" value="{{ $dateRange->max_date ?? date('Y-12-31') }}">
                </div>
            </div>
            <div class="param-card">
                <div class="param-icon calc"><i class="ri-calculator-line"></i></div>
                <div class="param-content">
                    <label>SD Type</label>
                    <select id="constructionSdType" class="fluent-select">
                        <option value="population">Population SD</option>
                        <option value="sample">Sample SD</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analysis Results -->
<div id="constructionResults" style="display: none;">
    <!-- Summary Cards -->
    <div class="analysis-summary-grid" id="constructionSummary"></div>

    <!-- Recommendation Cards -->
    <div class="recommendation-grid" id="constructionRecommendations"></div>

    <!-- Monthly Heatmap -->
    <div class="analysis-table-card">
        <div class="table-card-header">
            <div class="header-left">
                <i class="ri-calendar-2-line"></i>
                <div>
                    <h3>Monthly Construction Suitability</h3>
                    <p id="constructionDateRange">-</p>
                </div>
            </div>
        </div>
        <div class="table-card-body">
            <div class="monthly-heatmap" id="constructionMonthlyHeatmap"></div>
        </div>
    </div>

    <!-- Main Results Table -->
    <div class="analysis-table-card statistics-section">
        <div class="table-card-header">
            <div class="header-left">
                <i class="ri-building-2-line"></i>
                <div>
                    <h3>Construction Weather Analysis Results</h3>
                    <p>50% and 98% Reliability Levels</p>
                </div>
            </div>
            <div class="header-actions">
                <button type="button" class="fluent-btn fluent-btn-ghost" onclick="exportConstructionCSV()">
                    <i class="ri-download-2-line"></i> Export
                </button>
                <button type="button" class="fluent-btn fluent-btn-ghost" onclick="printConstructionAnalysis()">
                    <i class="ri-printer-line"></i> Print
                </button>
            </div>
        </div>
        <div class="table-card-body">
            <div class="analysis-table-wrapper">
                <table class="analysis-stats-table construction-table" id="constructionStatsTable">
                    <thead>
                        <tr class="header-main">
                            <th rowspan="3" class="col-station">Station</th>
                            <th rowspan="3">Years</th>
                            <th colspan="4" class="group-stats">Annual Statistics</th>
                            <th colspan="4" class="group-50">50% Reliability</th>
                            <th colspan="4" class="group-98">98% Reliability</th>
                        </tr>
                        <tr class="header-sub">
                            <th colspan="2">Temperature</th>
                            <th colspan="2">Rainfall</th>
                            <th colspan="2">Temp</th>
                            <th colspan="2">Days</th>
                            <th colspan="2">Temp</th>
                            <th colspan="2">Days</th>
                        </tr>
                        <tr class="header-cols">
                            <th>Max</th>
                            <th>Min</th>
                            <th>Total</th>
                            <th>Std</th>
                            <th>Max</th>
                            <th>Min</th>
                            <th>Dry</th>
                            <th>Work</th>
                            <th>Max</th>
                            <th>Min</th>
                            <th>Dry</th>
                            <th>Work</th>
                        </tr>
                    </thead>
                    <tbody id="constructionStatsTableBody"></tbody>
                </table>
            </div>
        </div>
        <div class="table-legend">
            <div class="legend-item"><span class="legend-dot high"></span> High Temperature</div>
            <div class="legend-item"><span class="legend-dot low"></span> Low Temperature</div>
            <div class="legend-item"><span class="legend-dot" style="background: #3B82F6;"></span> Rainfall</div>
            <div class="legend-item"><span class="legend-dot" style="background: #10B981;"></span> Working Days</div>
        </div>
        <div class="construction-formula-note">
            <strong>Construction Suitability Index (CSI) Formula:</strong><br>
            CSI = (Rain Score x 0.30) + (Temp Score x 0.25) + (Humidity Score x 0.20) + (Sunshine Score x 0.25)<br>
            <span class="formula-source">Based on Bangladesh climate patterns and RHD construction standards</span>
        </div>
    </div>
</div>

<!-- Empty State -->
<div id="constructionEmptyState" class="analysis-empty-state">
    <div class="empty-illustration">
        <div class="empty-circle">
            <i class="ri-building-2-line"></i>
        </div>
    </div>
    <h3>Construction Weather Analysis</h3>
    <p>Select stations, set the date range, and click <strong>"Run Analysis"</strong></p>
    <div class="empty-features">
        <div class="feature-item">
            <i class="ri-rainy-line"></i>
            <span>Rainfall Analysis</span>
        </div>
        <div class="feature-item">
            <i class="ri-temp-hot-line"></i>
            <span>Temperature Analysis</span>
        </div>
        <div class="feature-item">
            <i class="ri-sun-line"></i>
            <span>Working Days</span>
        </div>
        <div class="feature-item">
            <i class="ri-calendar-check-line"></i>
            <span>Best Construction Periods</span>
        </div>
    </div>
</div>

<!-- Construction Analysis Detail Modal -->
<div class="modal-overlay" id="constructionDetailModal">
    <div class="modal-container modal-xl">
        <div class="modal-header">
            <div class="modal-header-icon" style="background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);">
                <i class="ri-building-2-line"></i>
            </div>
            <div class="modal-header-content">
                <h2 class="modal-title" id="constructionDetailTitle">Station Analysis</h2>
                <p class="modal-subtitle" id="constructionDetailSubtitle">Construction Weather Analysis Details</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('constructionDetailModal')"><i class="ri-close-line"></i></button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <!-- Tabs -->
            <div class="construction-modal-tabs">
                <button type="button" class="construction-tab-btn active" data-tab="monthly">
                    <i class="ri-calendar-2-line"></i> Monthly Analysis
                </button>
                <button type="button" class="construction-tab-btn" data-tab="calendar">
                    <i class="ri-calendar-schedule-line"></i> Calendar View
                </button>
                <button type="button" class="construction-tab-btn" data-tab="yearly">
                    <i class="ri-line-chart-line"></i> Yearly Trends
                </button>
                <button type="button" class="construction-tab-btn" data-tab="formula">
                    <i class="ri-calculator-line"></i> Formula & Methodology
                </button>
            </div>

            <!-- Tab Content: Monthly -->
            <div class="construction-tab-content active" id="constructionTabMonthly">
                <div class="monthly-heatmap" id="constructionDetailHeatmap"></div>
                <div class="score-breakdown" id="constructionScoreBreakdown"></div>
                <div class="chart-container" style="height: 300px; margin-top: 24px;">
                    <canvas id="constructionMonthlyChart"></canvas>
                </div>
            </div>

            <!-- Tab Content: Calendar -->
            <div class="construction-tab-content" id="constructionTabCalendar">
                <div class="calendar-view-header">
                    <div class="calendar-legend">
                        <span class="legend-item excellent"><span class="legend-color"></span> Excellent (80+)</span>
                        <span class="legend-item good"><span class="legend-color"></span> Good (60-79)</span>
                        <span class="legend-item fair"><span class="legend-color"></span> Fair (40-59)</span>
                        <span class="legend-item poor"><span class="legend-color"></span> Poor (<40)</span>
                    </div>
                </div>
                <div class="construction-calendar-grid" id="constructionCalendarGrid"></div>
                <div class="calendar-summary" id="constructionCalendarSummary"></div>
            </div>

            <!-- Tab Content: Yearly -->
            <div class="construction-tab-content" id="constructionTabYearly">
                <div class="chart-container" style="height: 350px;">
                    <canvas id="constructionYearlyChart"></canvas>
                </div>
                <div class="yearly-data-table" id="constructionYearlyTable"></div>
            </div>

            <!-- Tab Content: Formula -->
            <div class="construction-tab-content" id="constructionTabFormula">
                <div class="formula-explanation" id="constructionFormulaExplanation"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="fluent-btn fluent-btn-secondary" onclick="closeModal('constructionDetailModal')">Close</button>
        </div>
    </div>
</div>
@endsection

@section('styles')
<!-- Daily Weather Modular CSS -->
<link href="/css/daily-weather/variables.css" rel="stylesheet">
<link href="/css/daily-weather/base.css" rel="stylesheet">
<link href="/css/daily-weather/analytics.css" rel="stylesheet">
<link href="/css/daily-weather/data.css?v={{ time() }}" rel="stylesheet">
<link href="/css/daily-weather/station-analysis.css" rel="stylesheet">
<link href="/css/daily-weather/pavement.css" rel="stylesheet">
<link href="/css/daily-weather/construction-weather.css" rel="stylesheet">
<link href="/css/daily-weather/multiselect.css?v={{ time() }}" rel="stylesheet">
<style>
/* Grid layout for 4 columns */
.params-grid-4 {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 16px;
}
.param-card-wide {
    grid-column: span 1;
}
.param-icon.station {
    background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    color: white;
}
@media (max-width: 1200px) {
    .params-grid-4 {
        grid-template-columns: 1fr 1fr;
    }
    .param-card-wide {
        grid-column: span 2;
    }
}
@media (max-width: 768px) {
    .params-grid-4 {
        grid-template-columns: 1fr;
    }
    .param-card-wide {
        grid-column: span 1;
    }
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="/js/daily-weather/multiselect.js?v={{ time() }}"></script>
<script>
// Utility functions
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function openModal(id) {
    console.log('Opening modal:', id);
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    console.log('Closing modal:', id);
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Modal overlay click to close
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay') && e.target.classList.contains('active')) {
        closeModal(e.target.id);
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            closeModal(modal.id);
        });
    }
});

// Station data for multiselect
const stationData = @json($stations);

// Construction Analysis URL
const constructionAnalysisUrl = "{{ route('admin.construction-weather.analysisData') }}";

// Initialize multiselect
let constructionStationMultiselect;
</script>
<script src="/js/daily-weather/construction-weather.js"></script>
<script>
// Initialize station multiselect
$(document).ready(function() {
    // Create station multiselect
    constructionStationMultiselect = createStationMultiselect('constructionStationSelect', stationData, {
        selectAllByDefault: true,
        placeholder: 'Select stations...',
        searchPlaceholder: 'Search stations...'
    });

    // Clear any stale cache and load fresh data
    clearConstructionCache();
    constructionDataLoaded = false;

    // Trigger analysis automatically using default loader
    setTimeout(function() {
        loadConstructionAnalysisDefault();
    }, 500);
});

// Override the station selection functions to use multiselect
function selectAllConstructionStations() {
    if (constructionStationMultiselect) {
        constructionStationMultiselect.selectAll();
    }
}

function deselectAllConstructionStations() {
    if (constructionStationMultiselect) {
        constructionStationMultiselect.deselectAll();
    }
}

// Override getSelectedStations to use multiselect
function getConstructionSelectedStations() {
    if (constructionStationMultiselect) {
        return constructionStationMultiselect.getSelectedValues();
    }
    return [];
}
</script>
@endsection
