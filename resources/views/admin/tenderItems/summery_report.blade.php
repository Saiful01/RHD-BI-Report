@extends('layouts.admin')
@section('title', 'Item Summary Report - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-pie-chart-2-line"></i>
        </span>
        Item Summary Report
    </h1>
</div>

<!-- Filter Card -->
<div class="filter-card mb-4">
    <div class="filter-header" id="toggle_filter">
        <div class="filter-header-left">
            <div class="filter-icon">
                <i class="ri-pie-chart-2-line"></i>
            </div>
            <div class="filter-header-text">
                <span class="filter-title">Report Filters</span>
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
        <div class="filter-grid">
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-hashtag"></i>
                    Tender ID
                </label>
                <select id="tender_id_filter" class="modern-select"></select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-map-2-line"></i>
                    Division
                </label>
                <select id="division_id" class="modern-select" multiple>
                    @foreach($divisions as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-building-line"></i>
                    Ministry
                </label>
                <select id="ministry" class="modern-select" multiple>
                    @foreach($ministries as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-map-pin-line"></i>
                    District
                </label>
                <select id="district" class="modern-select" multiple>
                    @foreach($districts as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-store-line"></i>
                    Supplier
                </label>
                <select id="supplier_name" class="modern-select" multiple></select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-barcode-line"></i>
                    Item Code
                </label>
                <select id="item_code" class="modern-select" multiple></select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-file-list-line"></i>
                    Item Name
                </label>
                <select id="item_name" class="modern-select" multiple></select>
            </div>
        </div>
        <div class="filter-actions">
            <button id="reset_button" class="filter-btn filter-btn-secondary">
                <i class="ri-refresh-line"></i>
                Reset
            </button>
            <button id="filter_button" class="filter-btn filter-btn-primary">
                <i class="ri-search-line"></i>
                Generate Report
            </button>
        </div>
    </div>
</div>

<div id="report_area">
    <div class="fluent-card">
        <div class="fluent-card-body text-center py-5">
            <div class="fluent-empty-state">
                <i class="ri-bar-chart-box-line" style="font-size: 48px; color: var(--fluent-gray-50);"></i>
                <p class="mt-3 text-secondary">Select filters and click "Search" to generate report</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function(){
    function select2AjaxMultiple(selector, url, placeholder) {
        $(selector).select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            multiple: true,
            minimumInputLength: 1,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data })
            }
        });
    }

    select2AjaxMultiple('#tender_id_filter', '/admin/tender-select-search', 'Type Tender ID...');
    select2AjaxMultiple('#supplier_name', '/admin/supplier-search', 'Select Supplier...');
    select2AjaxMultiple('#item_code', '/admin/item-code-search', 'Select Item Code...');
    select2AjaxMultiple('#item_name', '/admin/item-name-search', 'Select Item Name...');

    $('#division_id, #ministry, #district').select2({
        width: '100%',
        placeholder: 'Select...',
        allowClear: true,
        multiple: true
    });

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

    // Update active filters badge
    function updateActiveFilters() {
        let count = 0;
        if ($('#tender_id_filter').val() && $('#tender_id_filter').val().length) count++;
        if ($('#division_id').val() && $('#division_id').val().length) count++;
        if ($('#ministry').val() && $('#ministry').val().length) count++;
        if ($('#district').val() && $('#district').val().length) count++;
        if ($('#supplier_name').val() && $('#supplier_name').val().length) count++;
        if ($('#item_code').val() && $('#item_code').val().length) count++;
        if ($('#item_name').val() && $('#item_name').val().length) count++;

        const $badge = $('#active_filters');
        if (count > 0) {
            $badge.text(count + ' filter' + (count > 1 ? 's' : '') + ' active');
            $badge.addClass('active');
        } else {
            $badge.text('No filters');
            $badge.removeClass('active');
        }
    }

    function fetchReport() {
        let data = {
            tender_id_filter: $('#tender_id_filter').val(),
            division_id: $('#division_id').val(),
            ministry: $('#ministry').val(),
            district: $('#district').val(),
            supplier_name: $('#supplier_name').val(),
            item_code: $('#item_code').val(),
            item_name: $('#item_name').val()
        };

        $('#report_area').html(`
            <div class="fluent-card">
                <div class="fluent-card-body text-center py-5">
                    <div class="fluent-spinner-mini"></div>
                    <p class="mt-3 text-secondary">Loading report...</p>
                </div>
            </div>
        `);

        $.ajax({
            url: "{{ route('admin.tender-item.summeryReport') }}",
            data: data,
            success: function(response){
                let divisions = Object.keys(response.summary).filter(d => d).sort();

                if(divisions.length === 0) {
                    $('#report_area').html(`
                        <div class="fluent-card">
                            <div class="fluent-card-body text-center py-5">
                                <i class="ri-inbox-line" style="font-size: 48px; color: var(--fluent-gray-50);"></i>
                                <p class="mt-3 text-secondary">No data found for the selected filters</p>
                            </div>
                        </div>
                    `);
                    return;
                }

                // Summary Cards
                let summaryHtml = '<div class="summary-stats-grid mb-4">';
                divisions.forEach(function(division){
                    let count = response.summary[division];
                    summaryHtml += `
                        <div class="summary-stat-card">
                            <div class="summary-stat-icon">
                                <i class="ri-map-pin-2-line"></i>
                            </div>
                            <div class="summary-stat-content">
                                <span class="summary-stat-value">${count}</span>
                                <span class="summary-stat-label">${division}</span>
                            </div>
                        </div>
                    `;
                });
                summaryHtml += '</div>';

                // Report Tables by Division
                let reportHtml = '';
                let grouped = response.grouped;

                divisions.forEach(function(division){
                    let items = grouped[division];
                    let totalAmount = items.reduce((sum, row) => sum + (row.item_quantity * row.item_rate), 0);

                    reportHtml += `
                        <div class="fluent-card mb-4">
                            <div class="fluent-card-header">
                                <h3 class="fluent-card-title">
                                    <span class="fluent-stat-icon primary" style="width:28px;height:28px;font-size:14px;margin-right:8px;">
                                        <i class="ri-map-pin-2-line"></i>
                                    </span>
                                    ${division}
                                    <span class="fluent-badge fluent-badge-primary ml-2">${items.length} items</span>
                                </h3>
                                <div class="fluent-card-actions">
                                    <span class="fluent-badge fluent-badge-success">
                                        <i class="ri-money-dollar-circle-line mr-1"></i>
                                        Total: ${totalAmount.toLocaleString('en-BD', {minimumFractionDigits: 2})} BDT
                                    </span>
                                </div>
                            </div>
                            <div class="fluent-card-body p-0">
                                <div class="fluent-table-wrapper">
                                    <table class="fluent-table fluent-table-compact">
                                        <thead>
                                            <tr>
                                                <th style="width: 120px;">Tender ID</th>
                                                <th>Supplier</th>
                                                <th style="width: 100px;">Item Code</th>
                                                <th>Item Name</th>
                                                <th style="width: 100px;" class="text-right">Quantity</th>
                                                <th style="width: 100px;" class="text-right">Rate</th>
                                                <th style="width: 130px;" class="text-right">Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                    items.forEach(row => {
                        let total = row.item_quantity * row.item_rate;
                        reportHtml += `
                            <tr>
                                <td>
                                    <span class="fluent-badge fluent-badge-primary">${row.tender?.tenderid ?? 'N/A'}</span>
                                </td>
                                <td class="text-truncate" style="max-width: 200px;" title="${row.tender?.supplier_name ?? 'N/A'}">
                                    ${row.tender?.supplier_name ?? 'N/A'}
                                </td>
                                <td><code>${row.item_code}</code></td>
                                <td class="text-truncate" style="max-width: 200px;" title="${row.item_name}">
                                    ${row.item_name}
                                </td>
                                <td class="text-right font-mono">${parseFloat(row.item_quantity).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                                <td class="text-right font-mono">${parseFloat(row.item_rate).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                                <td class="text-right font-mono font-semibold">${total.toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                            </tr>`;
                    });

                    reportHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>`;
                });

                $('#report_area').html(summaryHtml + reportHtml);
            },
            error: function() {
                $('#report_area').html(`
                    <div class="fluent-card">
                        <div class="fluent-card-body text-center py-5">
                            <i class="ri-error-warning-line" style="font-size: 48px; color: var(--fluent-danger);"></i>
                            <p class="mt-3 text-danger">Error loading data. Please try again.</p>
                        </div>
                    </div>
                `);
            }
        });
    }

    $('#filter_button').on('click', function() {
        fetchReport();
        updateActiveFilters();
    });

    $('#reset_button').on('click', function(){
        $('#tender_id_filter, #division_id, #ministry, #district, #supplier_name, #item_code, #item_name')
            .val(null).trigger('change');
        updateActiveFilters();
        $('#report_area').html(`
            <div class="fluent-card">
                <div class="fluent-card-body text-center py-5">
                    <div class="fluent-empty-state">
                        <i class="ri-bar-chart-box-line" style="font-size: 48px; color: var(--fluent-gray-50);"></i>
                        <p class="mt-3 text-secondary">Select filters and click "Search" to generate report</p>
                    </div>
                </div>
            </div>
        `);
    });
});
</script>
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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
.modern-select {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    font-size: 14px;
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
.filter-btn-secondary {
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    border: 1px solid var(--fluent-gray-30);
}
.filter-btn-secondary:hover {
    background: var(--fluent-gray-20);
}

.summary-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}
.summary-stat-card {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-lg);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;
}
.summary-stat-card:hover {
    box-shadow: var(--fluent-shadow-8);
    transform: translateY(-2px);
}
.summary-stat-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}
.summary-stat-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.summary-stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--fluent-text-primary);
    line-height: 1.2;
}
.summary-stat-label {
    font-size: 12px;
    color: var(--fluent-text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fluent-table-compact td, .fluent-table-compact th {
    padding: 10px 12px;
}
.font-mono {
    font-family: 'Consolas', 'Monaco', monospace;
}
</style>
@endsection
