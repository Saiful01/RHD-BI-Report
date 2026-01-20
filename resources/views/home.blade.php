@extends('layouts.admin')
@section('title', 'Dashboard - ' . trans('panel.site_title'))

@section('styles')
<!-- Leaflet Map CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
/* Dashboard Map Styles */
.dashboard-map-container {
    background: var(--fluent-surface);
    border-radius: var(--fluent-radius-lg);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
    margin-top: 20px;
}
.dashboard-map-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--fluent-gray-20);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.dashboard-map-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 600;
    color: var(--fluent-text-primary);
}
.dashboard-map-title i {
    font-size: 20px;
    color: var(--fluent-primary);
}
.map-legend-inline {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 13px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.legend-marker {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.legend-marker.active { background: #10B981; }
.legend-marker.inactive { background: #9CA3AF; }
#bangladeshMap {
    height: 550px;
    width: 100%;
    background: #f8fafc;
}

/* Custom Markers */
.station-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    transition: transform 0.2s ease;
    cursor: pointer;
}
.station-marker:hover {
    transform: scale(1.2);
}
.station-marker.active {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}
.station-marker.inactive {
    background: linear-gradient(135deg, #9CA3AF 0%, #6B7280 100%);
}
.station-marker i {
    color: white;
    font-size: 12px;
}

/* Station Info Panel - Fixed position inside map */
.station-info-panel {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 320px;
    max-height: calc(100% - 20px);
    background: var(--fluent-surface);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
    z-index: 1000;
    overflow: hidden;
    display: none;
    animation: slideIn 0.25s ease;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
.station-info-panel.active {
    display: block;
}
.station-info-panel .panel-content {
    max-height: calc(530px - 20px);
    overflow-y: auto;
}
.station-info-panel .close-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    z-index: 15;
    transition: all 0.15s;
    backdrop-filter: blur(4px);
}
.station-info-panel .close-btn:hover {
    background: rgba(255,255,255,0.4);
    transform: scale(1.1);
}
.popup-card {
    font-family: var(--fluent-font-family);
}
.popup-header {
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    color: white !important;
    padding: 16px;
    padding-right: 45px;
}
.popup-header h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    color: white !important;
}
.popup-header h4 i {
    color: white !important;
    font-size: 18px;
}
.popup-header .status-badge {
    font-size: 10px;
    padding: 3px 10px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    margin-left: auto;
    color: white !important;
    font-weight: 500;
}
.popup-header .station-coords {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.popup-header .station-coords i {
    font-size: 12px;
}
.popup-body {
    padding: 0;
}

/* Section Styles */
.popup-section {
    padding: 14px 16px;
    border-bottom: 1px solid var(--fluent-gray-20);
}
.popup-section:last-child {
    border-bottom: none;
}
.popup-section-title {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fluent-text-secondary);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.popup-section-title i {
    font-size: 14px;
    color: var(--fluent-primary);
}

/* Stats Grid */
.popup-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.popup-stat {
    background: var(--fluent-gray-10);
    border-radius: 8px;
    padding: 10px 6px;
    text-align: center;
    border: 1px solid var(--fluent-gray-20);
}
.popup-stat .value {
    font-size: 16px;
    font-weight: 700;
    color: var(--fluent-text-primary);
    line-height: 1.2;
}
.popup-stat .value.temp-high { color: #EF4444; }
.popup-stat .value.temp-low { color: #3B82F6; }
.popup-stat .value.rain { color: #0EA5E9; }
.popup-stat .value.humidity { color: #8B5CF6; }
.popup-stat .value.sun { color: #F59E0B; }
.popup-stat .label {
    font-size: 10px;
    color: var(--fluent-text-secondary);
    margin-top: 3px;
}

/* Info List */
.popup-info-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.popup-info-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    padding: 4px 0;
}
.popup-info-item .label {
    color: var(--fluent-text-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.popup-info-item .label i {
    color: var(--fluent-primary);
    font-size: 14px;
}
.popup-info-item .value {
    font-weight: 600;
    color: var(--fluent-text-primary);
}

/* Week Trend Mini Chart */
.popup-trend {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    height: 40px;
    padding: 4px 0;
    gap: 3px;
}
.popup-trend-bar {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.popup-trend-bar .bar {
    width: 100%;
    border-radius: 2px 2px 0 0;
    min-height: 3px;
    transition: height 0.3s ease;
}
.popup-trend-bar .bar.max { background: linear-gradient(180deg, #EF4444 0%, #FCA5A5 100%); }
.popup-trend-bar .bar.min { background: linear-gradient(180deg, #3B82F6 0%, #93C5FD 100%); }
.popup-trend-bar .day {
    font-size: 8px;
    color: var(--fluent-text-secondary);
}

/* Yearly Summary */
.popup-year-summary {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.popup-year-item {
    background: var(--fluent-gray-10);
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid var(--fluent-gray-20);
}
.popup-year-item i {
    font-size: 18px;
}
.popup-year-item .content {
    flex: 1;
}
.popup-year-item .content .value {
    font-size: 14px;
    font-weight: 700;
    color: var(--fluent-text-primary);
}
.popup-year-item .content .label {
    font-size: 10px;
    color: var(--fluent-text-secondary);
    margin-top: 2px;
}

/* Actions */
.popup-actions {
    display: flex;
    gap: 10px;
    padding: 14px 16px;
    background: var(--fluent-gray-10);
    border-top: 1px solid var(--fluent-gray-20);
}
.popup-btn {
    flex: 1;
    padding: 10px 12px;
    border: none;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.15s ease;
}
.popup-btn.primary {
    background: var(--fluent-primary);
    color: white;
}
.popup-btn.primary:hover {
    background: #106EBE;
    transform: translateY(-1px);
}
.popup-btn.secondary {
    background: white;
    color: var(--fluent-text-primary);
    border: 1px solid var(--fluent-gray-30);
}
.popup-btn.secondary:hover {
    background: var(--fluent-gray-20);
}

/* Map Controls */
.leaflet-control-zoom {
    border: none !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
}
.leaflet-control-zoom a {
    background: white !important;
    color: var(--fluent-text-primary) !important;
    border: none !important;
}
.leaflet-control-zoom a:hover {
    background: var(--fluent-gray-10) !important;
}

/* Responsive */
@media (max-width: 768px) {
    #bangladeshMap {
        height: 400px;
    }
    .dashboard-map-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .map-legend-inline {
        flex-wrap: wrap;
    }
    .leaflet-popup-content {
        width: 260px !important;
        max-height: 300px;
    }
    .popup-stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .popup-year-summary {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

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
            <div class="fluent-stat-value" id="stationCount">{{ \App\Models\Station::count() }}</div>
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

<!-- Bangladesh Map -->
<div class="dashboard-map-container">
    <div class="dashboard-map-header">
        <div class="dashboard-map-title">
            <i class="ri-map-2-line"></i>
            Weather Stations Map - Bangladesh
        </div>
        <div class="map-legend-inline">
            <div class="legend-item">
                <span class="legend-marker active"></span>
                <span>Active Station</span>
            </div>
            <div class="legend-item">
                <span class="legend-marker inactive"></span>
                <span>Inactive Station</span>
            </div>
        </div>
    </div>
    <div style="position: relative;">
        <div id="bangladeshMap"></div>
        <!-- Station Info Panel -->
        <div id="stationInfoPanel" class="station-info-panel">
            <button class="close-btn" onclick="closeStationPanel()"><i class="ri-close-line"></i></button>
            <div class="panel-content" id="stationInfoContent"></div>
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
            @can('daily_weather_access')
            <a href="{{ route('admin.daily-weathers.index') }}" class="fluent-btn fluent-btn-primary">
                <i class="ri-bar-chart-box-line"></i>
                Weather Analytics
            </a>
            @endcan
            @can('station_access')
            <a href="{{ route('admin.stations.index') }}" class="fluent-btn fluent-btn-secondary">
                <i class="ri-map-pin-line"></i>
                Manage Stations
            </a>
            @endcan
            @can('tender_access')
            <a href="{{ route('admin.tender.index') }}" class="fluent-btn fluent-btn-secondary">
                <i class="ri-file-list-3-line"></i>
                Browse Tenders
            </a>
            @endcan
            @can('user_access')
            <a href="{{ route('admin.users.index') }}" class="fluent-btn fluent-btn-secondary">
                <i class="ri-user-line"></i>
                Manage Users
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
$(function() {
    initBangladeshMap();
});

function initBangladeshMap() {
    // Bangladesh bounds
    const bangladeshBounds = L.latLngBounds(
        L.latLng(20.5, 88.0),
        L.latLng(26.7, 92.7)
    );

    // Initialize map
    const map = L.map('bangladeshMap', {
        center: [23.8103, 90.4125],
        zoom: 7,
        minZoom: 6,
        maxZoom: 12,
        maxBounds: bangladeshBounds,
        maxBoundsViscosity: 1.0
    });

    // Light map tiles
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    // Load accurate Bangladesh boundary (GADM data hosted locally)
    fetch('/data/bangladesh.json')
        .then(response => response.json())
        .then(data => {
            // Add Bangladesh with accurate borders
            L.geoJSON(data, {
                style: {
                    fillColor: '#dbeafe',
                    fillOpacity: 0.35,
                    color: '#0078D4',
                    weight: 2,
                    opacity: 1
                }
            }).addTo(map);

            // Load stations after border
            loadStations(map);
        })
        .catch(err => {
            console.error('Failed to load Bangladesh boundary:', err);
            // If fetch fails, just load stations
            loadStations(map);
        });
}

function loadStations(map) {
    // Setup click handler to close panel when clicking map
    setupMapClickHandler(map);

    $.get("{{ route('admin.stations.mapData') }}", function(response) {
        if (response.success) {
            response.stations.forEach(station => {
                if (station.lat && station.lon) {
                    addStationMarker(map, station);
                }
            });
        }
    });
}

function addStationMarker(map, station) {
    const isActive = station.status === 'active';
    const markerSize = 24;

    // Create custom marker
    const icon = L.divIcon({
        className: 'custom-station-marker',
        html: `<div class="station-marker ${isActive ? 'active' : 'inactive'}" style="width:${markerSize}px;height:${markerSize}px;">
            <i class="ri-map-pin-2-fill"></i>
        </div>`,
        iconSize: [markerSize, markerSize],
        iconAnchor: [markerSize/2, markerSize/2]
    });

    // Create marker
    const marker = L.marker([station.lat, station.lon], { icon: icon }).addTo(map);

    // Click handler - show info panel
    marker.on('click', function(e) {
        // Build and show info panel content
        const panelContent = buildPopupContent(station, isActive);
        document.getElementById('stationInfoContent').innerHTML = panelContent;
        document.getElementById('stationInfoPanel').classList.add('active');
    });

    return marker;
}

// Close info panel when clicking on map
function setupMapClickHandler(map) {
    map.on('click', function() {
        closeStationPanel();
    });
}

// Close station panel
function closeStationPanel() {
    document.getElementById('stationInfoPanel').classList.remove('active');
}

function buildPopupContent(station, isActive) {
    // Latest weather section
    let latestHtml = '';
    if (station.latest_data) {
        latestHtml = `
            <div class="popup-section">
                <div class="popup-section-title">
                    <i class="ri-sun-cloudy-line"></i>
                    Latest Weather (${station.latest_data.date})
                </div>
                <div class="popup-stats-grid">
                    <div class="popup-stat">
                        <div class="value temp-high">${station.latest_data.max_temp}°</div>
                        <div class="label">Max Temp</div>
                    </div>
                    <div class="popup-stat">
                        <div class="value temp-low">${station.latest_data.min_temp}°</div>
                        <div class="label">Min Temp</div>
                    </div>
                    <div class="popup-stat">
                        <div class="value rain">${station.latest_data.rainfall}</div>
                        <div class="label">Rain (mm)</div>
                    </div>
                    <div class="popup-stat">
                        <div class="value humidity">${station.latest_data.humidity}%</div>
                        <div class="label">Humidity</div>
                    </div>
                    <div class="popup-stat">
                        <div class="value sun">${station.latest_data.sunshine}</div>
                        <div class="label">Sun (hrs)</div>
                    </div>
                    <div class="popup-stat">
                        <div class="value">${station.latest_data.dew_point}°</div>
                        <div class="label">Dew Point</div>
                    </div>
                </div>
            </div>
        `;
    }

    // Weekly trend
    let trendHtml = '';
    if (station.week_trend && station.week_trend.length > 0) {
        const maxTemp = Math.max(...station.week_trend.map(d => d.max));
        const minTemp = Math.min(...station.week_trend.map(d => d.min));
        const range = maxTemp - minTemp || 1;

        const barsHtml = station.week_trend.map(day => {
            const maxHeight = ((day.max - minTemp) / range) * 30 + 10;
            const minHeight = ((day.min - minTemp) / range) * 20 + 5;
            return `
                <div class="popup-trend-bar">
                    <div class="bar max" style="height:${maxHeight}px;" title="Max: ${day.max}°C"></div>
                    <div class="day">${day.date.split(' ')[1]}</div>
                </div>
            `;
        }).join('');

        trendHtml = `
            <div class="popup-section">
                <div class="popup-section-title">
                    <i class="ri-line-chart-line"></i>
                    Last 7 Days Temperature Trend
                </div>
                <div class="popup-trend">${barsHtml}</div>
            </div>
        `;
    }

    // Yearly stats
    let yearlyHtml = '';
    if (station.yearly_stats) {
        yearlyHtml = `
            <div class="popup-section">
                <div class="popup-section-title">
                    <i class="ri-calendar-line"></i>
                    ${station.yearly_stats.year} Year Summary
                </div>
                <div class="popup-year-summary">
                    <div class="popup-year-item">
                        <i class="ri-temp-hot-line" style="color:#EF4444;"></i>
                        <div class="content">
                            <div class="value">${station.yearly_stats.highest_temp}°C</div>
                            <div class="label">Highest Temp</div>
                        </div>
                    </div>
                    <div class="popup-year-item">
                        <i class="ri-temp-cold-line" style="color:#3B82F6;"></i>
                        <div class="content">
                            <div class="value">${station.yearly_stats.lowest_temp}°C</div>
                            <div class="label">Lowest Temp</div>
                        </div>
                    </div>
                    <div class="popup-year-item">
                        <i class="ri-rainy-line" style="color:#0EA5E9;"></i>
                        <div class="content">
                            <div class="value">${station.yearly_stats.total_rainfall} mm</div>
                            <div class="label">Total Rainfall</div>
                        </div>
                    </div>
                    <div class="popup-year-item">
                        <i class="ri-database-2-line" style="color:#8B5CF6;"></i>
                        <div class="content">
                            <div class="value">${station.yearly_stats.days_recorded}</div>
                            <div class="label">Days Recorded</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Station info
    let infoHtml = `
        <div class="popup-section">
            <div class="popup-section-title">
                <i class="ri-information-line"></i>
                Station Information
            </div>
            <div class="popup-info-list">
                ${station.elevation ? `
                <div class="popup-info-item">
                    <span class="label"><i class="ri-arrow-up-circle-line"></i> Elevation</span>
                    <span class="value">${station.elevation}m</span>
                </div>
                ` : ''}
                <div class="popup-info-item">
                    <span class="label"><i class="ri-database-line"></i> Total Records</span>
                    <span class="value">${station.record_stats.total_records.toLocaleString()}</span>
                </div>
                ${station.record_stats.first_date ? `
                <div class="popup-info-item">
                    <span class="label"><i class="ri-calendar-check-line"></i> Data Range</span>
                    <span class="value">${station.record_stats.first_date} - ${station.record_stats.last_date}</span>
                </div>
                ` : ''}
            </div>
        </div>
    `;

    return `
        <div class="popup-card">
            <div class="popup-header">
                <h4>
                    <i class="ri-map-pin-2-fill"></i>
                    ${station.name}
                    <span class="status-badge">${isActive ? 'Active' : 'Inactive'}</span>
                </h4>
                <div class="station-coords">
                    <span><i class="ri-map-pin-line"></i> ${station.lat.toFixed(4)}, ${station.lon.toFixed(4)}</span>
                </div>
            </div>
            <div class="popup-body">
                ${latestHtml}
                ${trendHtml}
                ${yearlyHtml}
                ${infoHtml}
            </div>
            <div class="popup-actions">
                <button class="popup-btn primary" onclick="window.location.href='/admin/daily-weathers?mode=stationAnalytics&station=${station.id}'">
                    <i class="ri-bar-chart-line"></i> Full Analytics
                </button>
                <button class="popup-btn secondary" onclick="window.location.href='/admin/stations?station=${station.id}'">
                    <i class="ri-settings-3-line"></i> Settings
                </button>
            </div>
        </div>
    `;
}
</script>
@endsection
