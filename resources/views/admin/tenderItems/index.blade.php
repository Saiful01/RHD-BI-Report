@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-archive-stack-line"></i>
        </span>
        Tender Item List
    </h1>
</div>

<!-- Filter Card -->
<div class="filter-card mb-4">
    <div class="filter-header" id="toggle_filter">
        <div class="filter-header-left">
            <div class="filter-icon">
                <i class="ri-filter-3-line"></i>
            </div>
            <div class="filter-header-text">
                <span class="filter-title">Search / Filter</span>
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
                <select id="division_id" class="modern-select">
                    <option value="">All Divisions</option>
                    @foreach($divisions as $id => $entry)
                        <option value="{{ $id }}">{{ $entry }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-store-line"></i>
                    Supplier Name
                </label>
                <select id="supplier_name" class="modern-select"></select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-barcode-line"></i>
                    Item Code
                </label>
                <select id="item_code" class="modern-select"></select>
            </div>
            <div class="filter-field">
                <label class="filter-label">
                    <i class="ri-file-list-line"></i>
                    Item Name
                </label>
                <select id="item_name" class="modern-select"></select>
            </div>
        </div>
        <div class="filter-actions">
            <button id="reset_button" class="filter-btn filter-btn-secondary">
                <i class="ri-refresh-line"></i>
                Reset
            </button>
            <button id="filter_button" class="filter-btn filter-btn-primary">
                <i class="ri-search-line"></i>
                Apply Filters
            </button>
        </div>
    </div>
</div>

<!-- Results Header -->
<div class="results-header mb-3">
    <div class="results-info">
        <span id="results_count" class="results-count">Loading...</span>
    </div>
    <div class="results-pagination" id="pagination_top"></div>
</div>

<!-- Loading State -->
<div id="loading_state" class="loading-card" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner">
            <i class="ri-loader-4-line"></i>
        </div>
        <p>Loading tender items...</p>
    </div>
</div>

<!-- Empty State -->
<div id="empty_state" class="empty-card" style="display: none;">
    <div class="empty-content">
        <div class="empty-icon">
            <i class="ri-archive-line"></i>
        </div>
        <h3>No Items Found</h3>
        <p>No tender items match your filter criteria. Try adjusting your filters.</p>
    </div>
</div>

<!-- Items Grid -->
<div id="items_grid" class="items-cards-grid"></div>

<!-- Results Footer -->
<div class="results-footer mt-4">
    <div class="results-pagination" id="pagination_bottom"></div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detail_modal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-header-left">
                <div class="modal-icon">
                    <i class="ri-file-list-3-line"></i>
                </div>
                <div>
                    <h3 class="modal-title">Item Details</h3>
                    <span class="modal-subtitle" id="modal_item_code"></span>
                </div>
            </div>
            <button class="modal-close" onclick="closeDetailModal()">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-body" id="modal_body">
            <!-- Content loaded dynamically -->
        </div>
        <div class="modal-footer">
            <button class="modal-btn-secondary" onclick="closeDetailModal()">Close</button>
        </div>
    </div>
</div>
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

/* Results Header */
.results-header, .results-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
.results-count {
    font-size: 14px;
    color: var(--fluent-text-secondary);
}
.results-count strong {
    color: var(--fluent-text-primary);
}

/* Loading Card */
.loading-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    padding: 80px 20px;
}
.loading-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.loading-spinner {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
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
.loading-content p {
    margin-top: 16px;
    color: var(--fluent-text-secondary);
}

/* Empty Card */
.empty-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    padding: 80px 20px;
}
.empty-content {
    text-align: center;
}
.empty-icon {
    width: 80px;
    height: 80px;
    background: var(--fluent-gray-20);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}
.empty-icon i {
    font-size: 36px;
    color: var(--fluent-text-tertiary);
}
.empty-content h3 {
    color: var(--fluent-text-primary);
    margin: 0 0 8px 0;
    font-size: 18px;
}
.empty-content p {
    color: var(--fluent-text-secondary);
    margin: 0;
}

/* Items Grid */
.items-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

/* Item Card */
.item-card {
    background: var(--fluent-bg-primary);
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
    transition: all 0.25s ease;
}
.item-card:hover {
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
    transform: translateY(-4px);
    border-color: var(--fluent-primary);
}

/* Card Top */
.card-top {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--fluent-gray-20);
}
.card-badge {
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Consolas', monospace;
    letter-spacing: 0.5px;
}
.card-tender {
    font-size: 12px;
    color: var(--fluent-text-secondary);
    font-weight: 500;
    background: var(--fluent-gray-20);
    padding: 4px 10px;
    border-radius: 6px;
}

/* Card Content */
.card-content {
    padding: 20px;
}
.card-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    margin: 0 0 12px 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 45px;
}
.card-supplier {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--fluent-text-secondary);
    font-size: 13px;
}
.card-supplier i {
    color: var(--fluent-primary);
    font-size: 16px;
}
.card-supplier span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Card Metrics */
.card-metrics {
    display: flex;
    align-items: stretch;
    background: var(--fluent-gray-10);
    border-top: 1px solid var(--fluent-gray-20);
}
.metric {
    flex: 1;
    padding: 16px 12px;
    text-align: center;
}
.metric.highlight {
    background: linear-gradient(135deg, rgba(16, 124, 16, 0.08) 0%, rgba(84, 176, 84, 0.08) 100%);
}
.metric.highlight .metric-value {
    color: #107C10;
}
.metric-divider {
    width: 1px;
    background: var(--fluent-gray-30);
}
.metric-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--fluent-text-primary);
    font-family: 'Consolas', monospace;
    margin-bottom: 4px;
}
.metric-label {
    font-size: 11px;
    color: var(--fluent-text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Pagination */
.pagination-container {
    display: flex;
    gap: 4px;
    align-items: center;
}
.page-btn {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--fluent-gray-40);
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-md);
    font-size: 13px;
    color: var(--fluent-text-primary);
    cursor: pointer;
    transition: all 0.15s ease;
}
.page-btn:hover:not(.active):not(:disabled) {
    background: var(--fluent-gray-20);
    border-color: var(--fluent-gray-60);
}
.page-btn.active {
    background: var(--fluent-primary);
    border-color: var(--fluent-primary);
    color: white;
}
.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.pagination-info {
    color: var(--fluent-text-secondary);
    font-size: 13px;
    padding: 0 12px;
}

/* Item Card Clickable */
.item-card {
    cursor: pointer;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.modal-overlay.show {
    display: flex;
    opacity: 1;
}
.modal-container {
    background: var(--fluent-bg-primary);
    border-radius: 16px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    transform: scale(0.95) translateY(20px);
    transition: transform 0.25s ease;
}
.modal-overlay.show .modal-container {
    transform: scale(1) translateY(0);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--fluent-gray-20);
    background: linear-gradient(135deg, rgba(0, 120, 212, 0.05) 0%, rgba(0, 188, 242, 0.05) 100%);
}
.modal-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.modal-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
}
.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    margin: 0;
}
.modal-subtitle {
    font-size: 13px;
    color: var(--fluent-text-secondary);
    font-family: 'Consolas', monospace;
}
.modal-close {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    border-radius: 10px;
    cursor: pointer;
    color: var(--fluent-text-secondary);
    font-size: 22px;
    transition: all 0.15s ease;
}
.modal-close:hover {
    background: var(--fluent-gray-20);
    color: var(--fluent-text-primary);
}
.modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--fluent-gray-20);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: var(--fluent-gray-10);
}
.modal-btn-secondary {
    padding: 10px 24px;
    border: 1px solid var(--fluent-gray-40);
    background: var(--fluent-bg-primary);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: var(--fluent-text-primary);
    cursor: pointer;
    transition: all 0.15s ease;
}
.modal-btn-secondary:hover {
    background: var(--fluent-gray-20);
}

/* Modal Loading */
.modal-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
}
.modal-loading i {
    font-size: 48px;
    color: var(--fluent-primary);
    animation: spin 1s linear infinite;
}
.modal-loading p {
    margin-top: 16px;
    color: var(--fluent-text-secondary);
}

/* Modal Detail Grid */
.detail-section {
    margin-bottom: 24px;
}
.detail-section:last-child {
    margin-bottom: 0;
}
.detail-section-title {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fluent-text-tertiary);
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--fluent-gray-20);
}
.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.detail-item.full-width {
    grid-column: span 2;
}
.detail-label {
    font-size: 12px;
    color: var(--fluent-text-tertiary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.detail-label i {
    color: var(--fluent-primary);
    font-size: 14px;
}
.detail-value {
    font-size: 14px;
    color: var(--fluent-text-primary);
    font-weight: 500;
}
.detail-value.mono {
    font-family: 'Consolas', monospace;
}
.detail-value.highlight {
    color: #107C10;
    font-weight: 600;
}

/* Amount Card */
.amount-card {
    background: linear-gradient(135deg, rgba(16, 124, 16, 0.08) 0%, rgba(84, 176, 84, 0.08) 100%);
    border: 1px solid rgba(16, 124, 16, 0.2);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}
.amount-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fluent-text-secondary);
    margin-bottom: 8px;
}
.amount-value {
    font-size: 28px;
    font-weight: 700;
    color: #107C10;
    font-family: 'Consolas', monospace;
}
.amount-suffix {
    font-size: 14px;
    color: var(--fluent-text-secondary);
    margin-left: 4px;
}

/* Mobile Responsive */
@media (max-width: 992px) {
    .fluent-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .fluent-page-actions {
        width: 100%;
        flex-wrap: wrap;
    }
}

@media (max-width: 768px) {
    .filter-card .filter-body {
        padding: 16px;
    }
    .filter-row {
        flex-direction: column;
        gap: 12px;
    }
    .filter-group {
        width: 100%;
    }
    .filter-actions {
        flex-direction: column;
        gap: 8px;
    }
    .filter-actions .fluent-btn {
        width: 100%;
        justify-content: center;
    }
    .item-card {
        padding: 16px;
    }
    .item-header {
        flex-direction: column;
        gap: 12px;
    }
    .item-badges {
        width: 100%;
        justify-content: flex-start;
    }
    .item-details-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .item-footer {
        flex-direction: column;
        gap: 12px;
    }
    .item-actions {
        width: 100%;
    }
    .item-actions .fluent-btn {
        flex: 1;
        justify-content: center;
    }
    .results-header {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    .pagination-container {
        justify-content: center;
    }
    /* Modals */
    .modal-container,
    .view-modal-container {
        width: 95% !important;
        max-width: 95% !important;
    }
    .modal-header,
    .view-header {
        padding: 16px;
    }
    .modal-body,
    .view-body {
        padding: 16px;
    }
    .modal-footer,
    .view-footer {
        flex-direction: column;
        gap: 8px;
        padding: 12px 16px;
    }
    .modal-footer .fluent-btn,
    .view-footer .fluent-btn {
        width: 100%;
        justify-content: center;
    }
    .form-row {
        flex-direction: column;
    }
    .form-col {
        width: 100%;
    }
    .amount-value {
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .fluent-page-title {
        font-size: 18px;
    }
}
</style>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    let currentPage = 1;
    const perPage = 12;

    function select2Ajax(selector, url, placeholder) {
        $(selector).select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: function (data) {
                    const results = data.map(item => ({
                        id: item.text ?? item.id,
                        text: item.text ?? item.id
                    }));
                    return { results: results };
                }
            }
        });
    }

    select2Ajax('#tender_id_filter', '/admin/tender-select-search', 'Type Tender ID...');
    select2Ajax('#supplier_name', '/admin/supplier-search', 'Type Supplier Name...');
    select2Ajax('#item_code', '/admin/item-code-search', 'Type Item Code...');
    select2Ajax('#item_name', '/admin/item-name-search', 'Type Item Name...');

    $('#division_id').select2({
        width: '100%',
        placeholder: 'Select Division',
        allowClear: true
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
        if ($('#tender_id_filter').val()) count++;
        if ($('#division_id').val()) count++;
        if ($('#supplier_name').val()) count++;
        if ($('#item_code').val()) count++;
        if ($('#item_name').val()) count++;

        const $badge = $('#active_filters');
        if (count > 0) {
            $badge.text(count + ' filter' + (count > 1 ? 's' : '') + ' active');
            $badge.addClass('active');
        } else {
            $badge.text('No filters');
            $badge.removeClass('active');
        }
    }

    function loadItemsData(page = 1) {
        currentPage = page;
        $('#loading_state').show();
        $('#items_grid').hide();
        $('#empty_state').hide();
        $('#pagination_top, #pagination_bottom').hide();

        $.ajax({
            url: "{{ route('admin.tender-item.index') }}",
            type: 'GET',
            data: {
                page: page,
                per_page: perPage,
                tender_id_filter: $('#tender_id_filter').val(),
                division_id: $('#division_id').val(),
                supplier_name: $('#supplier_name').val(),
                item_code: $('#item_code').val(),
                item_name: $('#item_name').val()
            },
            success: function(response) {
                $('#loading_state').hide();

                if (response.data && response.data.length > 0) {
                    renderItemCards(response.data);
                    renderPagination(response);
                    $('#results_count').html(`Showing <strong>${response.from || 0}-${response.to || 0}</strong> of <strong>${response.total}</strong> items`);
                    $('#items_grid').show();
                    $('#pagination_top, #pagination_bottom').show();
                } else {
                    $('#empty_state').show();
                    $('#results_count').html('No items found');
                }
            },
            error: function() {
                $('#loading_state').hide();
                $('#empty_state').show();
                $('#results_count').html('Error loading items');
            }
        });
    }

    function renderItemCards(data) {
        let html = '';
        data.forEach(function(item) {
            const tenderId = item.tender ? item.tender.tenderid : '-';
            const supplier = item.tender ? (item.tender.supplier_name || '-') : '-';
            const total = (parseFloat(item.item_quantity) * parseFloat(item.item_rate)).toFixed(2);

            html += `
                <div class="item-card" onclick="openDetailModal(${item.id})">
                    <div class="card-top">
                        <div class="card-badge">${escapeHtml(item.item_code || '-')}</div>
                        <div class="card-tender">#${escapeHtml(tenderId)}</div>
                    </div>
                    <div class="card-content">
                        <h4 class="card-title" title="${escapeHtml(item.item_name || '')}">${escapeHtml(item.item_name || '-')}</h4>
                        <div class="card-supplier" title="${escapeHtml(supplier)}">
                            <i class="ri-store-2-line"></i>
                            <span>${escapeHtml(supplier)}</span>
                        </div>
                    </div>
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-value">${formatNumber(item.item_quantity)}</div>
                            <div class="metric-label">${escapeHtml(item.item_unit || 'Unit')}</div>
                        </div>
                        <div class="metric-divider"></div>
                        <div class="metric">
                            <div class="metric-value">${formatNumber(item.item_rate)}</div>
                            <div class="metric-label">Rate</div>
                        </div>
                        <div class="metric-divider"></div>
                        <div class="metric highlight">
                            <div class="metric-value">${formatNumber(total)}</div>
                            <div class="metric-label">Total BDT</div>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#items_grid').html(html);
    }

    function renderPagination(response) {
        const { current_page, last_page } = response;
        let html = '<div class="pagination-container">';

        // Previous button
        html += `<button class="page-btn" onclick="goToPage(${current_page - 1})" ${current_page === 1 ? 'disabled' : ''}><i class="ri-arrow-left-s-line"></i></button>`;

        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        if (startPage > 1) {
            html += `<button class="page-btn" onclick="goToPage(1)">1</button>`;
            if (startPage > 2) html += `<span style="padding: 0 8px;">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="page-btn ${i === current_page ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        }

        if (endPage < last_page) {
            if (endPage < last_page - 1) html += `<span style="padding: 0 8px;">...</span>`;
            html += `<button class="page-btn" onclick="goToPage(${last_page})">${last_page}</button>`;
        }

        // Next button
        html += `<button class="page-btn" onclick="goToPage(${current_page + 1})" ${current_page === last_page ? 'disabled' : ''}><i class="ri-arrow-right-s-line"></i></button>`;
        html += '</div>';

        $('#pagination_top, #pagination_bottom').html(html);
    }

    window.goToPage = function(page) {
        loadItemsData(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    $('#filter_button').on('click', function() {
        loadItemsData(1);
        updateActiveFilters();
    });

    $('#reset_button').on('click', function() {
        $('#tender_id_filter, #division_id, #supplier_name, #item_code, #item_name')
            .val(null).trigger('change');
        loadItemsData(1);
        updateActiveFilters();
    });

    loadItemsData();
});

// Modal Functions
function openDetailModal(itemId) {
    const modal = document.getElementById('detail_modal');
    const modalBody = document.getElementById('modal_body');
    const modalItemCode = document.getElementById('modal_item_code');

    // Show modal with loading
    modal.classList.add('show');
    modalItemCode.textContent = 'Loading...';
    modalBody.innerHTML = `
        <div class="modal-loading">
            <i class="ri-loader-4-line"></i>
            <p>Loading item details...</p>
        </div>
    `;

    // Fetch item data
    $.ajax({
        url: `/admin/tender-item/${itemId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.item) {
                renderDetailModal(response.item);
            } else {
                modalBody.innerHTML = '<p style="text-align: center; color: var(--fluent-text-secondary);">Failed to load item details</p>';
            }
        },
        error: function() {
            modalBody.innerHTML = '<p style="text-align: center; color: var(--fluent-text-secondary);">Error loading item details</p>';
        }
    });

    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
    const modal = document.getElementById('detail_modal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

function renderDetailModal(item) {
    const modalItemCode = document.getElementById('modal_item_code');
    const modalBody = document.getElementById('modal_body');

    const tenderId = item.tender ? item.tender.tenderid : '-';
    const supplier = item.tender ? (item.tender.supplier_name || '-') : '-';
    const division = item.division ? item.division.division : '-';
    const ministry = item.tender ? (item.tender.ministry_division || '-') : '-';
    const district = item.tender ? (item.tender.procuring_entity_district || '-') : '-';
    const quantity = parseFloat(item.item_quantity || 0);
    const rate = parseFloat(item.item_rate || 0);
    const total = (quantity * rate).toFixed(2);

    modalItemCode.textContent = item.item_code || '-';

    modalBody.innerHTML = `
        <div class="detail-section">
            <div class="detail-section-title">Item Information</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-barcode-line"></i> Item Code</span>
                    <span class="detail-value mono">${escapeHtml(item.item_code || '-')}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-ruler-line"></i> Unit</span>
                    <span class="detail-value">${escapeHtml(item.item_unit || '-')}</span>
                </div>
                <div class="detail-item full-width">
                    <span class="detail-label"><i class="ri-file-list-line"></i> Item Name</span>
                    <span class="detail-value">${escapeHtml(item.item_name || '-')}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Tender Information</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-hashtag"></i> Tender ID</span>
                    <span class="detail-value mono">${escapeHtml(tenderId)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-map-2-line"></i> Division</span>
                    <span class="detail-value">${escapeHtml(division)}</span>
                </div>
                <div class="detail-item full-width">
                    <span class="detail-label"><i class="ri-store-line"></i> Supplier</span>
                    <span class="detail-value">${escapeHtml(supplier)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-building-line"></i> Ministry</span>
                    <span class="detail-value">${escapeHtml(ministry)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-map-pin-line"></i> District</span>
                    <span class="detail-value">${escapeHtml(district)}</span>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Pricing Details</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-stack-line"></i> Quantity</span>
                    <span class="detail-value mono">${formatNumber(quantity)} ${escapeHtml(item.item_unit || '')}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><i class="ri-price-tag-line"></i> Unit Rate</span>
                    <span class="detail-value mono">${formatNumber(rate)} BDT</span>
                </div>
            </div>
            <div class="amount-card" style="margin-top: 16px;">
                <div class="amount-label">Total Amount</div>
                <div class="amount-value">${formatNumber(total)}<span class="amount-suffix">BDT</span></div>
            </div>
        </div>
    `;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailModal();
    }
});

// Close modal when clicking outside
document.getElementById('detail_modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailModal();
    }
});
</script>
@endsection
