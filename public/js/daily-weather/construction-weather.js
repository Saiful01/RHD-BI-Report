/**
 * Construction Weather Analysis JavaScript
 * Multi-year statistical analysis for RHD construction planning
 */

// ==================== CONSTRUCTION WEATHER ANALYSIS ====================
let constructionDataLoaded = false;
let constructionAnalysisData = [];
let constructionLoadingTimer = null;
const CONSTRUCTION_CACHE_KEY = 'constructionAnalysisCache';
const CONSTRUCTION_CACHE_EXPIRY = 30 * 60 * 1000; // 30 minutes

function selectAllConstructionStations() {
    $('.construction-station-cb').prop('checked', true);
}

function deselectAllConstructionStations() {
    $('.construction-station-cb').prop('checked', false);
}

// Cache functions
function getConstructionCache() {
    try {
        const cached = localStorage.getItem(CONSTRUCTION_CACHE_KEY);
        if (!cached) return null;
        const parsed = JSON.parse(cached);
        if (Date.now() - parsed.timestamp > CONSTRUCTION_CACHE_EXPIRY) {
            localStorage.removeItem(CONSTRUCTION_CACHE_KEY);
            return null;
        }
        return parsed.data;
    } catch (e) {
        return null;
    }
}

function setConstructionCache(data) {
    try {
        localStorage.setItem(CONSTRUCTION_CACHE_KEY, JSON.stringify({
            data: data,
            timestamp: Date.now()
        }));
    } catch (e) {
        console.warn('Failed to cache construction data');
    }
}

function clearConstructionCache() {
    localStorage.removeItem(CONSTRUCTION_CACHE_KEY);
}

// Loading state management
function showConstructionLoading(message = 'Loading construction analysis...') {
    $('#constructionResults').hide();
    $('#constructionEmptyState').hide();

    // Show loading overlay with message
    $('#loadingOverlay').addClass('active').show();
    $('#loadingOverlay .loading-content p').text(message);

    // Start timer for long loading feedback
    let seconds = 0;
    constructionLoadingTimer = setInterval(function() {
        seconds++;
        if (seconds >= 5 && seconds < 15) {
            $('#loadingOverlay .loading-content p').text('Processing multi-year data... (' + seconds + 's)');
        } else if (seconds >= 15 && seconds < 30) {
            $('#loadingOverlay .loading-content p').text('Calculating statistics for all stations... (' + seconds + 's)');
        } else if (seconds >= 30) {
            $('#loadingOverlay .loading-content p').text('Almost done, please wait... (' + seconds + 's)');
        }
    }, 1000);
}

function hideConstructionLoading() {
    if (constructionLoadingTimer) {
        clearInterval(constructionLoadingTimer);
        constructionLoadingTimer = null;
    }
    $('#loadingOverlay').removeClass('active').hide();
    $('#loadingOverlay .loading-content p').text('Loading weather data...');
}

function loadConstructionAnalysisDefault() {
    if (constructionDataLoaded) return;
    constructionDataLoaded = true;

    // Select all stations by default
    selectAllConstructionStations();

    // Check cache first
    const cached = getConstructionCache();
    if (cached && cached.data && cached.data.length > 0) {
        renderConstructionFromData(cached);
        return;
    }

    // Show loading
    showConstructionLoading('Loading cached analysis data...');

    // Load with default parameters
    const params = new URLSearchParams();
    params.append('sd_type', $('#constructionSdType').val() || 'population');

    $.ajax({
        url: constructionAnalysisUrl + '?' + params.toString(),
        method: 'GET',
        timeout: 120000, // 2 minute timeout
        success: function(res) {
            hideConstructionLoading();

            if (!res.success || !res.data || res.data.length === 0) {
                $('#constructionResults').hide();
                $('#constructionEmptyState').show();
                $('#constructionEmptyState h3').text('No Data Found');
                $('#constructionEmptyState p').html('No weather data available for analysis.<br>Try adjusting the date range.');
                return;
            }

            setConstructionCache(res);
            renderConstructionFromData(res);
        },
        error: function(xhr, status, error) {
            hideConstructionLoading();
            $('#constructionEmptyState').show();
            $('#constructionResults').hide();

            if (status === 'timeout') {
                $('#constructionEmptyState h3').text('Request Timeout');
                $('#constructionEmptyState p').html('The analysis is taking too long.<br>Try selecting fewer stations or a smaller date range.');
            } else {
                $('#constructionEmptyState h3').text('Error Loading Data');
                $('#constructionEmptyState p').html('Failed to load analysis data.<br>Please try again.');
            }
        }
    });
}

function renderConstructionFromData(res) {
    const fromDate = res.summary.from_date;
    const toDate = res.summary.to_date;

    $('#constructionDateRange').text(`Data from ${formatDate(fromDate)} to ${formatDate(toDate)}`);

    // Update form fields from response
    if (res.summary.from_date) $('#constructionFromDate').val(res.summary.from_date);
    if (res.summary.to_date) $('#constructionToDate').val(res.summary.to_date);

    // Render summary
    renderConstructionSummary(res.summary);

    // Render recommendations
    renderConstructionRecommendations(res.summary);

    // Render monthly heatmap (first station as example)
    if (res.data.length > 0) {
        renderConstructionMonthlyHeatmap(res.data[0].monthly_stats);
    }

    // Render stats table
    renderConstructionStatsTable(res.data);

    $('#constructionResults').show();
    $('#constructionEmptyState').hide();
}

function runConstructionAnalysis() {
    // Clear cache for fresh analysis
    clearConstructionCache();

    // Get stations from multiselect if available, otherwise fall back to checkboxes
    let stationIds = [];
    if (typeof constructionStationMultiselect !== 'undefined' && constructionStationMultiselect) {
        stationIds = constructionStationMultiselect.getSelectedValues();
    } else if (typeof getConstructionSelectedStations === 'function') {
        stationIds = getConstructionSelectedStations();
    } else {
        $('.construction-station-cb:checked').each(function() {
            stationIds.push($(this).val());
        });
    }

    if (stationIds.length === 0) {
        alert('Please select at least one station');
        return;
    }

    const fromDate = $('#constructionFromDate').val();
    const toDate = $('#constructionToDate').val();

    if (!fromDate || !toDate) {
        alert('Please select a date range');
        return;
    }

    const params = new URLSearchParams();
    stationIds.forEach(id => params.append('stations[]', id));
    params.append('from_date', fromDate);
    params.append('to_date', toDate);
    params.append('sd_type', $('#constructionSdType').val());

    // Show loading with appropriate message
    const stationCount = stationIds.length;
    showConstructionLoading(`Analyzing ${stationCount} station${stationCount > 1 ? 's' : ''}...`);

    $.ajax({
        url: constructionAnalysisUrl + '?' + params.toString(),
        method: 'GET',
        timeout: 180000, // 3 minute timeout for manual analysis
        success: function(res) {
            hideConstructionLoading();

            if (!res.success || !res.data || res.data.length === 0) {
                $('#constructionResults').hide();
                $('#constructionEmptyState').show();
                $('#constructionEmptyState h3').text('No Data Found');
                $('#constructionEmptyState p').html('No weather data found for the selected criteria.<br>Try adjusting the date range or stations.');
                return;
            }

            renderConstructionFromData(res);
        },
        error: function(xhr, status, error) {
            hideConstructionLoading();
            $('#constructionEmptyState').show();
            $('#constructionResults').hide();

            if (status === 'timeout') {
                $('#constructionEmptyState h3').text('Analysis Timeout');
                $('#constructionEmptyState p').html('The analysis is taking too long to complete.<br><strong>Suggestions:</strong><ul style="text-align:left;margin-top:10px;"><li>Select fewer stations (try 5-10)</li><li>Use a smaller date range (try 5 years)</li><li>Try again later</li></ul>');
            } else {
                $('#constructionEmptyState h3').text('Analysis Failed');
                $('#constructionEmptyState p').html('Failed to complete the analysis.<br>Please try again.');
            }
        }
    });
}

function renderConstructionSummary(summary) {
    let html = '';

    html += `
        <div class="summary-stat-card rainfall">
            <div class="summary-stat-icon"><i class="ri-rainy-line"></i></div>
            <div class="summary-stat-content">
                <div class="summary-stat-value">${summary.avg_annual_rainfall || '-'} mm</div>
                <div class="summary-stat-label">Avg Annual Rainfall</div>
            </div>
        </div>
    `;

    html += `
        <div class="summary-stat-card dry-days">
            <div class="summary-stat-icon"><i class="ri-sun-line"></i></div>
            <div class="summary-stat-content">
                <div class="summary-stat-value">${summary.avg_dry_days || '-'}</div>
                <div class="summary-stat-label">Avg Dry Days/Year</div>
            </div>
        </div>
    `;

    html += `
        <div class="summary-stat-card working-days">
            <div class="summary-stat-icon"><i class="ri-tools-line"></i></div>
            <div class="summary-stat-content">
                <div class="summary-stat-value">${summary.avg_working_days || '-'}</div>
                <div class="summary-stat-label">Avg Working Days/Year</div>
            </div>
        </div>
    `;

    html += `
        <div class="summary-stat-card stations">
            <div class="summary-stat-icon"><i class="ri-map-pin-line"></i></div>
            <div class="summary-stat-content">
                <div class="summary-stat-value">${summary.station_count}</div>
                <div class="summary-stat-label">Stations Analyzed</div>
            </div>
        </div>
    `;

    $('#constructionSummary').html(html);
}

function renderConstructionRecommendations(summary) {
    const excellent = summary.overall_excellent_months || [];
    const good = summary.overall_good_months || [];
    const avoid = summary.overall_avoid_months || [];

    let html = '';

    html += `
        <div class="recommendation-card excellent">
            <div class="rec-icon"><i class="ri-checkbox-circle-line"></i></div>
            <div class="rec-title">Excellent</div>
            <div class="rec-months">
                ${excellent.length > 0 ? excellent.map(m => `<span class="month-chip">${m}</span>`).join('') : '<span class="month-chip">-</span>'}
            </div>
        </div>
    `;

    html += `
        <div class="recommendation-card good">
            <div class="rec-icon"><i class="ri-thumb-up-line"></i></div>
            <div class="rec-title">Good</div>
            <div class="rec-months">
                ${good.length > 0 ? good.map(m => `<span class="month-chip">${m}</span>`).join('') : '<span class="month-chip">-</span>'}
            </div>
        </div>
    `;

    html += `
        <div class="recommendation-card fair">
            <div class="rec-icon"><i class="ri-error-warning-line"></i></div>
            <div class="rec-title">Fair</div>
            <div class="rec-months">
                <span class="month-chip">Transitional</span>
            </div>
        </div>
    `;

    html += `
        <div class="recommendation-card avoid">
            <div class="rec-icon"><i class="ri-close-circle-line"></i></div>
            <div class="rec-title">Avoid</div>
            <div class="rec-months">
                ${avoid.length > 0 ? avoid.map(m => `<span class="month-chip">${m}</span>`).join('') : '<span class="month-chip">-</span>'}
            </div>
        </div>
    `;

    $('#constructionRecommendations').html(html);
}

function renderConstructionMonthlyHeatmap(monthlyStats) {
    if (!monthlyStats || monthlyStats.length === 0) {
        console.warn('No monthly stats data to render');
        return;
    }

    console.log('Rendering monthly heatmap with', monthlyStats.length, 'months:', monthlyStats.map(m => m.month_short));

    let html = '';
    monthlyStats.forEach(function(month) {
        const csi = month.csi;
        const ratingLabel = csi.rating === 'excellent' ? 'Excellent for construction' :
                          csi.rating === 'good' ? 'Good for construction' :
                          csi.rating === 'fair' ? 'Fair - some limitations' :
                          'Poor - avoid construction';
        const tooltipText = `${month.month_short}: CSI Score ${csi.csi.toFixed(0)}/100 - ${ratingLabel}`;
        html += `
            <div class="month-cell" data-rating="${csi.rating}" onclick="showMonthDetail(${month.month})" title="${tooltipText}">
                <div class="month-name">${month.month_short}</div>
                <div class="month-csi">${csi.csi.toFixed(0)}</div>
                <div class="month-rating">${csi.rating.replace('_', ' ')}</div>
            </div>
        `;
    });

    $('#constructionMonthlyHeatmap').html(html);
}

function renderConstructionStatsTable(data) {
    constructionAnalysisData = data;

    let tbody = '';
    data.forEach(function(station, index) {
        const annual = station.annual_stats;
        tbody += `
            <tr class="construction-row-clickable" data-index="${index}" title="Click to view details" style="cursor: pointer;">
                <td class="col-station">${station.station_name}</td>
                <td>${station.years_analyzed}</td>
                <td class="high">${annual.max_temp_avg?.mean || '-'}</td>
                <td class="low">${annual.min_temp_avg?.mean || '-'}</td>
                <td>${annual.annual_rainfall?.mean || '-'}</td>
                <td>${annual.annual_rainfall?.std || '-'}</td>
                <td class="high">${annual.max_temp_avg?.reliability_50 || '-'}</td>
                <td class="low">${annual.min_temp_avg?.reliability_50 || '-'}</td>
                <td>${annual.dry_days?.reliability_50 || '-'}</td>
                <td class="csi-good">${annual.working_days?.reliability_50 || '-'}</td>
                <td class="high">${annual.max_temp_avg?.reliability_98 || '-'}</td>
                <td class="low">${annual.min_temp_avg?.reliability_98 || '-'}</td>
                <td>${annual.dry_days?.reliability_98 || '-'}</td>
                <td class="csi-fair">${annual.working_days?.reliability_98 || '-'}</td>
            </tr>
        `;
    });

    $('#constructionStatsTableBody').html(tbody);
}

// Construction table row click - using event delegation for reliability
$(document).on('click', '.construction-row-clickable', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const index = parseInt($(this).data('index'), 10);
    console.log('Construction row clicked, index:', index);
    openConstructionDetailModal(index);
});

function exportConstructionCSV() {
    const table = document.getElementById('constructionStatsTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(function(row) {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(function(col) {
            let text = col.innerText.replace(/"/g, '""');
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'construction_weather_analysis_' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}

function printConstructionAnalysis() {
    const printContent = document.getElementById('constructionResults').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Construction Weather Analysis Report</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                th { background: #2d3748; color: white; }
                .high { color: #e74c3c; }
                .low { color: #3498db; }
                .analysis-summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
                .recommendation-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body>
            <h1 style="text-align: center; margin-bottom: 24px;">Construction Weather Analysis Report</h1>
            <p style="text-align: center; color: #666;">Generated on ${new Date().toLocaleDateString()}</p>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// ==================== CONSTRUCTION DETAIL MODAL ====================
let constructionMonthlyChart = null;
let constructionYearlyChart = null;

function openConstructionDetailModal(index) {
    const data = constructionAnalysisData[index];
    if (!data) return;

    $('#constructionDetailTitle').text(data.station_name);
    $('#constructionDetailSubtitle').text(`Lat: ${data.lat}° | Lon: ${data.lon}° | Elev: ${data.elev}m | ${data.years_analyzed} years analyzed`);

    // Reset tabs
    $('.construction-tab-btn').removeClass('active').first().addClass('active');
    $('.construction-tab-content').removeClass('active').first().addClass('active');

    // Open modal FIRST so canvas elements are visible
    openModal('constructionDetailModal');

    // Render charts AFTER modal is visible (small delay for DOM update)
    setTimeout(function() {
        // Render weather statistics summary
        renderConstructionWeatherStats(data.annual_stats, data.monthly_stats);

        // Render monthly heatmap in modal
        renderConstructionDetailHeatmap(data.monthly_stats);

        // Render score breakdown
        renderConstructionScoreBreakdown(data.monthly_stats);

        // Render monthly chart
        renderConstructionMonthlyChartModal(data.monthly_stats);

        // Render calendar view
        renderConstructionCalendarView(data.monthly_stats, data.annual_stats);

        // Render yearly chart
        renderConstructionYearlyChartModal(data.annual_stats.yearly_data);

        // Render formula explanation
        renderConstructionFormulaExplanation(data);
    }, 50);
}

$(document).on('click', '.construction-tab-btn', function() {
    const tab = $(this).data('tab');
    $('.construction-tab-btn').removeClass('active');
    $(this).addClass('active');
    $('.construction-tab-content').removeClass('active');
    $(`#constructionTab${tab.charAt(0).toUpperCase() + tab.slice(1)}`).addClass('active');
});

function renderConstructionWeatherStats(annualStats, monthlyStats) {
    if (!annualStats) return;

    // Calculate averages from monthly stats if available
    let avgTemp = '-';
    if (monthlyStats && monthlyStats.length > 0) {
        const maxTemps = monthlyStats.map(m => m.max_temp?.mean || 0).filter(v => v > 0);
        const minTemps = monthlyStats.map(m => m.min_temp?.mean || 0).filter(v => v > 0);
        if (maxTemps.length > 0 && minTemps.length > 0) {
            const avgMax = maxTemps.reduce((a, b) => a + b, 0) / maxTemps.length;
            const avgMin = minTemps.reduce((a, b) => a + b, 0) / minTemps.length;
            avgTemp = ((avgMax + avgMin) / 2).toFixed(1);
        }
    }

    const maxTemp = annualStats.max_temp_avg || {};
    const minTemp = annualStats.min_temp_avg || {};
    const maxTempExtreme = annualStats.max_temp_extreme || {};
    const minTempExtreme = annualStats.min_temp_extreme || {};
    const rainfall = annualStats.annual_rainfall || {};

    let html = `
        <div class="weather-stat-card">
            <span class="tooltip-trigger"><i class="ri-question-line"></i></span>
            <div class="tooltip-content">
                <div class="tooltip-header">
                    <div class="tooltip-header-icon temp-max"><i class="ri-temp-hot-line"></i></div>
                    <div class="tooltip-header-text">
                        <h4>Maximum Temperature</h4>
                        <span>Daily highest temperature readings</span>
                    </div>
                </div>
                <div class="tooltip-body">
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">What it measures</div>
                        <p>The highest temperature recorded each day, averaged across all years. High temperatures affect worker productivity and material curing.</p>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">Values explained</div>
                        <div class="tooltip-values-grid">
                            <div class="tooltip-value-item">
                                <div class="label">Mean (Average)</div>
                                <div class="value high">${maxTemp.mean || '-'}°C</div>
                            </div>
                            <div class="tooltip-value-item">
                                <div class="label">Extreme High</div>
                                <div class="value high">${maxTempExtreme.mean || '-'}°C</div>
                            </div>
                        </div>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">98% Reliability</div>
                        <p>98% of the time, max temperature will be at or below this value.</p>
                        <div class="tooltip-formula">98% = Mean + (2.055 × SD) = ${maxTemp.reliability_98 || '-'}°C</div>
                    </div>
                    <div class="tooltip-note">
                        <i class="ri-lightbulb-line"></i>
                        <span>For construction, temperatures above 35°C may require heat safety measures.</span>
                    </div>
                </div>
            </div>
            <div class="weather-stat-header">
                <div class="weather-stat-icon temp-max">
                    <i class="ri-temp-hot-line"></i>
                </div>
                <span class="weather-stat-title">Max Temperature</span>
            </div>
            <div class="weather-stat-values">
                <div class="weather-stat-main">
                    <span class="weather-stat-value">${maxTemp.mean || '-'}</span>
                    <span class="weather-stat-unit">°C avg</span>
                </div>
                <div class="weather-stat-details">
                    <span class="weather-stat-detail high">
                        <i class="ri-arrow-up-line"></i> Extreme: ${maxTempExtreme.mean || '-'}°C
                    </span>
                    <span class="weather-stat-detail">
                        <i class="ri-percent-line"></i> 98%: ${maxTemp.reliability_98 || '-'}°C
                    </span>
                </div>
            </div>
        </div>

        <div class="weather-stat-card">
            <span class="tooltip-trigger"><i class="ri-question-line"></i></span>
            <div class="tooltip-content">
                <div class="tooltip-header">
                    <div class="tooltip-header-icon temp-min"><i class="ri-temp-cold-line"></i></div>
                    <div class="tooltip-header-text">
                        <h4>Minimum Temperature</h4>
                        <span>Daily lowest temperature readings</span>
                    </div>
                </div>
                <div class="tooltip-body">
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">What it measures</div>
                        <p>The lowest temperature recorded each day (usually at night/early morning), averaged across all years. Important for concrete curing and frost considerations.</p>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">Values explained</div>
                        <div class="tooltip-values-grid">
                            <div class="tooltip-value-item">
                                <div class="label">Mean (Average)</div>
                                <div class="value low">${minTemp.mean || '-'}°C</div>
                            </div>
                            <div class="tooltip-value-item">
                                <div class="label">Extreme Low</div>
                                <div class="value low">${minTempExtreme.mean || '-'}°C</div>
                            </div>
                        </div>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">98% Reliability</div>
                        <p>98% of the time, min temperature will be at or above this value.</p>
                        <div class="tooltip-formula">98% = Mean - (2.055 × SD) = ${minTemp.reliability_98 || '-'}°C</div>
                    </div>
                    <div class="tooltip-note">
                        <i class="ri-lightbulb-line"></i>
                        <span>Concrete should not be placed when temperature is below 5°C without special precautions.</span>
                    </div>
                </div>
            </div>
            <div class="weather-stat-header">
                <div class="weather-stat-icon temp-min">
                    <i class="ri-temp-cold-line"></i>
                </div>
                <span class="weather-stat-title">Min Temperature</span>
            </div>
            <div class="weather-stat-values">
                <div class="weather-stat-main">
                    <span class="weather-stat-value">${minTemp.mean || '-'}</span>
                    <span class="weather-stat-unit">°C avg</span>
                </div>
                <div class="weather-stat-details">
                    <span class="weather-stat-detail low">
                        <i class="ri-arrow-down-line"></i> Extreme: ${minTempExtreme.mean || '-'}°C
                    </span>
                    <span class="weather-stat-detail">
                        <i class="ri-percent-line"></i> 98%: ${minTemp.reliability_98 || '-'}°C
                    </span>
                </div>
            </div>
        </div>

        <div class="weather-stat-card">
            <span class="tooltip-trigger"><i class="ri-question-line"></i></span>
            <div class="tooltip-content">
                <div class="tooltip-header">
                    <div class="tooltip-header-icon temp-avg"><i class="ri-thermometer-line"></i></div>
                    <div class="tooltip-header-text">
                        <h4>Average Temperature</h4>
                        <span>Daily mean temperature</span>
                    </div>
                </div>
                <div class="tooltip-body">
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">What it measures</div>
                        <p>The average of daily maximum and minimum temperatures. This gives a general indication of the thermal environment for construction activities.</p>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">Calculation method</div>
                        <div class="tooltip-formula">Avg Temp = (Max Temp + Min Temp) / 2</div>
                        <div class="tooltip-values-grid">
                            <div class="tooltip-value-item">
                                <div class="label">Calculated Average</div>
                                <div class="value">${avgTemp}°C</div>
                            </div>
                            <div class="tooltip-value-item">
                                <div class="label">Daily Range</div>
                                <div class="value">${minTemp.mean || '-'} - ${maxTemp.mean || '-'}°C</div>
                            </div>
                        </div>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">Ideal conditions</div>
                        <p>15-30°C is generally optimal for most construction activities including concrete work and asphalt paving.</p>
                    </div>
                    <div class="tooltip-note">
                        <i class="ri-lightbulb-line"></i>
                        <span>This value is used in the CSI Temperature Score calculation (25% weight).</span>
                    </div>
                </div>
            </div>
            <div class="weather-stat-header">
                <div class="weather-stat-icon temp-avg">
                    <i class="ri-thermometer-line"></i>
                </div>
                <span class="weather-stat-title">Average Temperature</span>
            </div>
            <div class="weather-stat-values">
                <div class="weather-stat-main">
                    <span class="weather-stat-value">${avgTemp}</span>
                    <span class="weather-stat-unit">°C</span>
                </div>
                <div class="weather-stat-details">
                    <span class="weather-stat-detail high">
                        <i class="ri-arrow-up-line"></i> High: ${maxTemp.mean || '-'}°C
                    </span>
                    <span class="weather-stat-detail low">
                        <i class="ri-arrow-down-line"></i> Low: ${minTemp.mean || '-'}°C
                    </span>
                </div>
            </div>
        </div>

        <div class="weather-stat-card">
            <span class="tooltip-trigger"><i class="ri-question-line"></i></span>
            <div class="tooltip-content">
                <div class="tooltip-header">
                    <div class="tooltip-header-icon rainfall"><i class="ri-rainy-line"></i></div>
                    <div class="tooltip-header-text">
                        <h4>Annual Rainfall</h4>
                        <span>Total precipitation per year</span>
                    </div>
                </div>
                <div class="tooltip-body">
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">What it measures</div>
                        <p>Total rainfall accumulated over a year, measured in millimeters. This directly impacts the number of workable days for construction.</p>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">Statistical values</div>
                        <div class="tooltip-values-grid">
                            <div class="tooltip-value-item">
                                <div class="label">Mean (Average)</div>
                                <div class="value rainfall">${rainfall.mean || '-'} mm</div>
                            </div>
                            <div class="tooltip-value-item">
                                <div class="label">Std Deviation</div>
                                <div class="value">±${rainfall.std || '-'} mm</div>
                            </div>
                        </div>
                    </div>
                    <div class="tooltip-section">
                        <div class="tooltip-section-title">98% Reliability</div>
                        <p>98% of years will have rainfall at or below this amount.</p>
                        <div class="tooltip-formula">98% = Mean + (2.055 × SD) = ${rainfall.reliability_98 || '-'} mm</div>
                    </div>
                    <div class="tooltip-note">
                        <i class="ri-lightbulb-line"></i>
                        <span>Rainfall has the highest weight (30%) in the CSI formula as it most directly affects construction feasibility.</span>
                    </div>
                </div>
            </div>
            <div class="weather-stat-header">
                <div class="weather-stat-icon rainfall">
                    <i class="ri-rainy-line"></i>
                </div>
                <span class="weather-stat-title">Annual Rainfall</span>
            </div>
            <div class="weather-stat-values">
                <div class="weather-stat-main">
                    <span class="weather-stat-value">${rainfall.mean || '-'}</span>
                    <span class="weather-stat-unit">mm/year</span>
                </div>
                <div class="weather-stat-details">
                    <span class="weather-stat-detail">
                        <i class="ri-add-line"></i> SD: ±${rainfall.std || '-'}mm
                    </span>
                    <span class="weather-stat-detail">
                        <i class="ri-percent-line"></i> 98%: ${rainfall.reliability_98 || '-'}mm
                    </span>
                </div>
            </div>
        </div>
    `;

    $('#constructionWeatherStats').html(html);
}

function renderConstructionDetailHeatmap(monthlyStats) {
    if (!monthlyStats || monthlyStats.length === 0) return;

    let html = '';
    monthlyStats.forEach(function(month) {
        const csi = month.csi;
        const tooltipText = `CSI ${csi.csi.toFixed(0)}: Rain(${csi.rainfall_score.toFixed(0)}) + Temp(${csi.temperature_score.toFixed(0)}) + Humidity(${csi.humidity_score.toFixed(0)}) + Sun(${csi.sunshine_score.toFixed(0)})`;
        html += `
            <div class="month-cell" data-rating="${csi.rating}" title="${tooltipText}">
                <div class="month-name">${month.month_short}</div>
                <div class="month-csi">${csi.csi.toFixed(0)}</div>
                <div class="month-rating">${csi.rating.replace('_', ' ')}</div>
            </div>
        `;
    });

    $('#constructionDetailHeatmap').html(html);
}

function renderConstructionScoreBreakdown(monthlyStats) {
    if (!monthlyStats || monthlyStats.length === 0) return;

    // Calculate average scores across all months
    let avgRain = 0, avgTemp = 0, avgHumidity = 0, avgSunshine = 0;
    monthlyStats.forEach(function(month) {
        avgRain += month.csi.rainfall_score;
        avgTemp += month.csi.temperature_score;
        avgHumidity += month.csi.humidity_score;
        avgSunshine += month.csi.sunshine_score;
    });

    const count = monthlyStats.length;
    avgRain = (avgRain / count).toFixed(0);
    avgTemp = (avgTemp / count).toFixed(0);
    avgHumidity = (avgHumidity / count).toFixed(0);
    avgSunshine = (avgSunshine / count).toFixed(0);

    // Calculate overall CSI
    const overallCSI = (parseFloat(avgRain) * 0.30 + parseFloat(avgTemp) * 0.25 + parseFloat(avgHumidity) * 0.20 + parseFloat(avgSunshine) * 0.25).toFixed(0);

    const html = `
        <div class="score-item rain" title="Rainfall Score: Based on monthly rainfall. Lower rainfall = higher score. Weight: 30%">
            <div class="score-icon"><i class="ri-rainy-line"></i></div>
            <div class="score-label">Rain Score <span class="score-weight">(30%)</span></div>
            <div class="score-value" style="color: #3B82F6;">${avgRain}</div>
        </div>
        <div class="score-item temp" title="Temperature Score: Based on average temperature. Optimal range 15-30°C scores highest. Weight: 25%">
            <div class="score-icon"><i class="ri-temp-hot-line"></i></div>
            <div class="score-label">Temp Score <span class="score-weight">(25%)</span></div>
            <div class="score-value" style="color: #EF4444;">${avgTemp}</div>
        </div>
        <div class="score-item humidity" title="Humidity Score: Based on relative humidity. Lower humidity = higher score for construction. Weight: 20%">
            <div class="score-icon"><i class="ri-drop-line"></i></div>
            <div class="score-label">Humidity Score <span class="score-weight">(20%)</span></div>
            <div class="score-value" style="color: #8B5CF6;">${avgHumidity}</div>
        </div>
        <div class="score-item sunshine" title="Sunshine Score: Based on sunshine hours. More sunshine = better for construction activities. Weight: 25%">
            <div class="score-icon"><i class="ri-sun-line"></i></div>
            <div class="score-label">Sunshine Score <span class="score-weight">(25%)</span></div>
            <div class="score-value" style="color: #F59E0B;">${avgSunshine}</div>
        </div>
    `;

    $('#constructionScoreBreakdown').html(html);
}

function renderConstructionMonthlyChartModal(monthlyStats) {
    if (!monthlyStats || monthlyStats.length === 0) return;

    const ctx = document.getElementById('constructionMonthlyChart');
    if (!ctx) return;

    if (constructionMonthlyChart) {
        constructionMonthlyChart.destroy();
    }

    const labels = monthlyStats.map(m => m.month_short);
    const csiData = monthlyStats.map(m => m.csi.csi);
    const rainfallData = monthlyStats.map(m => m.rainfall.mean);

    constructionMonthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'CSI Score',
                    data: csiData,
                    backgroundColor: csiData.map(v => {
                        if (v >= 80) return 'rgba(16, 185, 129, 0.8)';
                        if (v >= 60) return 'rgba(59, 130, 246, 0.8)';
                        if (v >= 40) return 'rgba(245, 158, 11, 0.8)';
                        return 'rgba(239, 68, 68, 0.8)';
                    }),
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Rainfall (mm)',
                    data: rainfallData,
                    type: 'line',
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    min: 0,
                    max: 100,
                    title: { display: true, text: 'CSI Score' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    min: 0,
                    title: { display: true, text: 'Rainfall (mm)' },
                    grid: { drawOnChartArea: false }
                }
            },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
}

function renderConstructionYearlyChartModal(yearlyData) {
    if (!yearlyData || yearlyData.length === 0) return;

    const ctx = document.getElementById('constructionYearlyChart');
    if (!ctx) return;

    if (constructionYearlyChart) {
        constructionYearlyChart.destroy();
    }

    const labels = yearlyData.map(y => y.year);
    const rainfallData = yearlyData.map(y => y.total_rainfall);
    const workingDaysData = yearlyData.map(y => y.working_days);
    const dryDaysData = yearlyData.map(y => y.dry_days);

    constructionYearlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Rainfall (mm)',
                    data: rainfallData,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Working Days',
                    data: workingDaysData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                },
                {
                    label: 'Dry Days',
                    data: dryDaysData,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Rainfall (mm)' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    title: { display: true, text: 'Days' },
                    grid: { drawOnChartArea: false }
                }
            },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Render yearly data table
    let tableHtml = `
        <table class="analysis-stats-table" style="margin-top: 20px; font-size: 12px;">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Total Days</th>
                    <th>Dry Days</th>
                    <th>Working Days</th>
                    <th>Total Rainfall (mm)</th>
                    <th>Avg Max Temp</th>
                    <th>Avg Min Temp</th>
                </tr>
            </thead>
            <tbody>
    `;

    yearlyData.forEach(function(y) {
        tableHtml += `
            <tr>
                <td><strong>${y.year}</strong></td>
                <td>${y.total_days}</td>
                <td>${y.dry_days}</td>
                <td>${y.working_days}</td>
                <td>${y.total_rainfall}</td>
                <td class="high">${y.max_temp_avg}</td>
                <td class="low">${y.min_temp_avg}</td>
            </tr>
        `;
    });

    tableHtml += '</tbody></table>';
    $('#constructionYearlyTable').html(tableHtml);
}

function renderConstructionFormulaExplanation(data) {
    const annual = data.annual_stats;

    const html = `
        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon input"><i class="ri-database-2-line"></i></div>
                <h4>Input Data</h4>
            </div>
            <div class="formula-section-body">
                <div class="formula-grid">
                    <div class="formula-item">
                        <span class="item-label">Station</span>
                        <span class="item-value">${data.station_name}</span>
                    </div>
                    <div class="formula-item">
                        <span class="item-label">Years Analyzed</span>
                        <span class="item-value">${data.years_analyzed}</span>
                    </div>
                    <div class="formula-item">
                        <span class="item-label">Latitude</span>
                        <span class="item-value">${data.lat}°</span>
                    </div>
                    <div class="formula-item">
                        <span class="item-label">Longitude</span>
                        <span class="item-value">${data.lon}°</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon calc"><i class="ri-calculator-line"></i></div>
                <h4>Construction Suitability Index (CSI) Formula</h4>
            </div>
            <div class="formula-section-body">
                <div class="formula-box">
                    <div class="formula-title">CSI Calculation</div>
                    <div class="formula-text">
                        CSI = (Rain Score × 0.30) + (Temp Score × 0.25) + (Humidity Score × 0.20) + (Sunshine Score × 0.25)
                    </div>
                </div>

                <h5 style="margin: 20px 0 12px;">Score Weighting (Bangladesh-Specific)</h5>
                <div class="weights-display">
                    <div class="weight-item rain">
                        <i class="ri-rainy-line" style="color: #3B82F6;"></i>
                        <span>Rainfall: 30%</span>
                        <div class="weight-bar" style="--weight-pct: 100%;"></div>
                    </div>
                    <div class="weight-item temp">
                        <i class="ri-temp-hot-line" style="color: #EF4444;"></i>
                        <span>Temperature: 25%</span>
                        <div class="weight-bar" style="--weight-pct: 83%;"></div>
                    </div>
                    <div class="weight-item humidity">
                        <i class="ri-drop-line" style="color: #8B5CF6;"></i>
                        <span>Humidity: 20%</span>
                        <div class="weight-bar" style="--weight-pct: 67%;"></div>
                    </div>
                    <div class="weight-item sunshine">
                        <i class="ri-sun-line" style="color: #F59E0B;"></i>
                        <span>Sunshine: 25%</span>
                        <div class="weight-bar" style="--weight-pct: 83%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon calc"><i class="ri-percent-line"></i></div>
                <h4>Reliability Calculation</h4>
            </div>
            <div class="formula-section-body">
                <div class="formula-box">
                    <div class="formula-title">50% Reliability (Average Conditions)</div>
                    <div class="formula-text">Value<sub>50%</sub> = Mean (μ)</div>
                </div>
                <div class="formula-box" style="margin-top: 12px;">
                    <div class="formula-title">98% Reliability (Conservative Planning)</div>
                    <div class="formula-text">
                        Value<sub>98%</sub> = μ ± (2.055 × σ)<br>
                        <small style="color: rgba(255,255,255,0.6);">+ for max values, - for min values and day counts</small>
                    </div>
                </div>

                <div class="calculation-step" style="margin-top: 20px;">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <div class="step-label">Calculate yearly values for each metric</div>
                        <div class="step-formula">For each year: aggregate daily data into yearly totals/averages</div>
                    </div>
                </div>
                <div class="calculation-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <div class="step-label">Calculate mean across all years</div>
                        <div class="step-formula">μ = Σx / n</div>
                    </div>
                </div>
                <div class="calculation-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <div class="step-label">Calculate standard deviation</div>
                        <div class="step-formula">σ = √[Σ(x - μ)² / (n-1)]</div>
                    </div>
                </div>
                <div class="calculation-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <div class="step-label">Apply reliability factor</div>
                        <div class="step-formula">98% Reliability = μ ± (2.055 × σ)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon result"><i class="ri-file-chart-line"></i></div>
                <h4>Results Summary</h4>
            </div>
            <div class="formula-section-body">
                <div class="formula-grid">
                    <div class="formula-item high">
                        <span class="item-label">Max Temp (98%)</span>
                        <span class="item-value">${annual.max_temp_avg?.reliability_98 || '-'}°C</span>
                    </div>
                    <div class="formula-item low">
                        <span class="item-label">Min Temp (98%)</span>
                        <span class="item-value">${annual.min_temp_avg?.reliability_98 || '-'}°C</span>
                    </div>
                    <div class="formula-item">
                        <span class="item-label">Annual Rainfall (98%)</span>
                        <span class="item-value">${annual.annual_rainfall?.reliability_98 || '-'} mm</span>
                    </div>
                    <div class="formula-item">
                        <span class="item-label">Working Days (98%)</span>
                        <span class="item-value">${annual.working_days?.reliability_98 || '-'} days</span>
                    </div>
                </div>

                <div class="verification-badge">
                    <i class="ri-checkbox-circle-line"></i>
                    Based on ${data.years_analyzed} years of historical data
                </div>
            </div>
        </div>

        <div class="formula-section">
            <div class="formula-section-header">
                <div class="section-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;"><i class="ri-calendar-2-line"></i></div>
                <h4>Bangladesh Construction Seasons</h4>
            </div>
            <div class="formula-section-body">
                <div class="formula-grid">
                    <div class="formula-item" style="border-left-color: #10B981;">
                        <span class="item-label">Winter (Nov-Feb)</span>
                        <span class="item-value" style="color: #10B981;">Excellent</span>
                    </div>
                    <div class="formula-item" style="border-left-color: #F59E0B;">
                        <span class="item-label">Pre-Monsoon (Mar-May)</span>
                        <span class="item-value" style="color: #F59E0B;">Limited</span>
                    </div>
                    <div class="formula-item" style="border-left-color: #EF4444;">
                        <span class="item-label">Monsoon (Jun-Sep)</span>
                        <span class="item-value" style="color: #EF4444;">Avoid</span>
                    </div>
                    <div class="formula-item" style="border-left-color: #3B82F6;">
                        <span class="item-label">Post-Monsoon (Oct)</span>
                        <span class="item-value" style="color: #3B82F6;">Transitional</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#constructionFormulaExplanation').html(html);
}

// ==================== CALENDAR VIEW ====================
function renderConstructionCalendarView(monthlyStats, annualStats) {
    if (!monthlyStats || monthlyStats.length === 0) return;

    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'];
    const weekDays = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

    // Create a sample year calendar (non-leap year has 365 days)
    let calendarHtml = '';

    monthlyStats.forEach(function(monthData, idx) {
        const monthNum = monthData.month || (idx + 1);
        const csi = monthData.csi;
        const rating = csi.rating;
        const daysInMonth = new Date(2024, monthNum, 0).getDate(); // Using 2024 as sample (leap year)
        const firstDay = new Date(2024, monthNum - 1, 1).getDay();

        calendarHtml += `
            <div class="calendar-month-card" data-rating="${rating}">
                <div class="calendar-month-header">
                    <div class="month-title">${monthNames[monthNum - 1]}</div>
                    <div class="month-csi-badge ${rating}">${csi.csi.toFixed(0)}</div>
                </div>
                <div class="calendar-month-stats">
                    <div class="stat-mini">
                        <i class="ri-rainy-line"></i>
                        <span>${monthData.rainfall?.mean || '-'}mm</span>
                    </div>
                    <div class="stat-mini">
                        <i class="ri-sun-line"></i>
                        <span>${monthData.dry_days?.mean || '-'}d</span>
                    </div>
                    <div class="stat-mini">
                        <i class="ri-temp-hot-line"></i>
                        <span>${monthData.max_temp?.mean || '-'}°</span>
                    </div>
                </div>
                <div class="calendar-weekdays">
                    ${weekDays.map(d => `<span>${d}</span>`).join('')}
                </div>
                <div class="calendar-days">
                    ${generateCalendarDays(firstDay, daysInMonth, rating)}
                </div>
                <div class="calendar-month-footer">
                    <span class="season-tag ${monthData.season}">${monthData.season_name || monthData.season}</span>
                </div>
            </div>
        `;
    });

    $('#constructionCalendarGrid').html(calendarHtml);

    // Render summary
    renderCalendarSummary(monthlyStats, annualStats);
}

function generateCalendarDays(firstDay, totalDays, rating) {
    let html = '';

    // Add empty cells for days before first day of month
    for (let i = 0; i < firstDay; i++) {
        html += '<span class="day empty"></span>';
    }

    // Add days
    for (let day = 1; day <= totalDays; day++) {
        html += `<span class="day ${rating}">${day}</span>`;
    }

    return html;
}

function renderCalendarSummary(monthlyStats, annualStats) {
    // Count months by rating
    let excellent = 0, good = 0, fair = 0, poor = 0;
    let bestMonths = [];
    let worstMonths = [];

    monthlyStats.forEach(function(m) {
        const rating = m.csi.rating;
        const csi = m.csi.csi;

        if (rating === 'excellent') {
            excellent++;
            bestMonths.push({ name: m.month_short, csi: csi });
        } else if (rating === 'good') {
            good++;
            bestMonths.push({ name: m.month_short, csi: csi });
        } else if (rating === 'fair') {
            fair++;
        } else {
            poor++;
            worstMonths.push({ name: m.month_short, csi: csi });
        }
    });

    // Sort best months by CSI descending
    bestMonths.sort((a, b) => b.csi - a.csi);
    worstMonths.sort((a, b) => a.csi - b.csi);

    const html = `
        <div class="calendar-summary-grid">
            <div class="summary-card excellent">
                <div class="summary-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div class="summary-content">
                    <div class="summary-value">${excellent + good}</div>
                    <div class="summary-label">Favorable Months</div>
                </div>
            </div>
            <div class="summary-card warning">
                <div class="summary-icon"><i class="ri-alert-line"></i></div>
                <div class="summary-content">
                    <div class="summary-value">${poor}</div>
                    <div class="summary-label">Months to Avoid</div>
                </div>
            </div>
            <div class="summary-card best">
                <div class="summary-icon"><i class="ri-star-line"></i></div>
                <div class="summary-content">
                    <div class="summary-value">${bestMonths.slice(0, 3).map(m => m.name).join(', ') || '-'}</div>
                    <div class="summary-label">Best Construction Period</div>
                </div>
            </div>
            <div class="summary-card worst">
                <div class="summary-icon"><i class="ri-forbid-line"></i></div>
                <div class="summary-content">
                    <div class="summary-value">${worstMonths.slice(0, 3).map(m => m.name).join(', ') || '-'}</div>
                    <div class="summary-label">Avoid Construction</div>
                </div>
            </div>
        </div>
        <div class="calendar-timeline">
            <div class="timeline-header">
                <span class="timeline-title"><i class="ri-calendar-check-line"></i> Annual Construction Timeline</span>
            </div>
            <div class="timeline-bar">
                ${monthlyStats.map(m => `
                    <div class="timeline-segment ${m.csi.rating}" title="${m.month_short}: CSI ${m.csi.csi.toFixed(0)}">
                        <span class="segment-label">${m.month_short.substring(0, 1)}</span>
                    </div>
                `).join('')}
            </div>
            <div class="timeline-labels">
                <span>Jan</span>
                <span>Jun</span>
                <span>Dec</span>
            </div>
        </div>
    `;

    $('#constructionCalendarSummary').html(html);
}

// Initialize when document ready
$(document).ready(function() {
    $('#runConstructionAnalysis').on('click', runConstructionAnalysis);

    // Initialize tooltip positioning
    initWeatherStatTooltips();
});

// Tooltip positioning for weather stat cards
function initWeatherStatTooltips() {
    // Create a tooltip container in body if not exists
    if ($('#weatherTooltipContainer').length === 0) {
        $('body').append('<div id="weatherTooltipContainer"></div>');
    }

    $(document).on('mouseenter', '.weather-stat-card .tooltip-trigger', function(e) {
        const trigger = $(this);
        const card = trigger.closest('.weather-stat-card');
        const tooltipContent = card.find('.tooltip-content');

        if (tooltipContent.length === 0) return;

        // Clone tooltip content and move to body container
        const container = $('#weatherTooltipContainer');
        container.empty().html(tooltipContent.html());

        // Get trigger position
        const triggerRect = trigger[0].getBoundingClientRect();
        const tooltipWidth = 320;
        const padding = 12;

        // Calculate position - prefer below the trigger
        let top = triggerRect.bottom + padding;
        let left = triggerRect.left - (tooltipWidth / 2) + (triggerRect.width / 2);

        // Adjust if tooltip goes off right edge
        if (left + tooltipWidth > window.innerWidth - padding) {
            left = window.innerWidth - tooltipWidth - padding;
        }

        // Adjust if tooltip goes off left edge
        if (left < padding) {
            left = padding;
        }

        container.css({
            position: 'fixed',
            top: top + 'px',
            left: left + 'px',
            width: tooltipWidth + 'px',
            zIndex: 2147483647,
            opacity: 1,
            visibility: 'visible',
            pointerEvents: 'auto'
        }).addClass('weather-tooltip-visible');

        // Store reference to current trigger
        container.data('trigger', trigger);
    });

    $(document).on('mouseleave', '.weather-stat-card .tooltip-trigger', function(e) {
        const container = $('#weatherTooltipContainer');

        // Small delay to allow moving to tooltip
        setTimeout(function() {
            if (!container.is(':hover')) {
                container.removeClass('weather-tooltip-visible').css({
                    opacity: 0,
                    visibility: 'hidden',
                    pointerEvents: 'none'
                });
            }
        }, 150);
    });

    $(document).on('mouseleave', '#weatherTooltipContainer', function(e) {
        $(this).removeClass('weather-tooltip-visible').css({
            opacity: 0,
            visibility: 'hidden',
            pointerEvents: 'none'
        });
    });
}
