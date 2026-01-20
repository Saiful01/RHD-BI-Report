@extends('layouts.admin')
@section('title', 'Tender Management - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-file-list-3-line"></i>
        </span>
        Tender Management
    </h1>
</div>

<!-- Tab Navigation -->
<div class="tender-tabs mb-4">
    <button class="tender-tab active" data-tab="tenders">
        <i class="ri-file-list-3-line"></i>
        <span>Tenders</span>
    </button>
    <button class="tender-tab" data-tab="items">
        <i class="ri-archive-stack-line"></i>
        <span>Tender Items</span>
    </button>
</div>

<!-- ================================== -->
<!-- TAB 1: TENDERS -->
<!-- ================================== -->
<div id="tenders_tab" class="tab-content active">
    <!-- Filter Card -->
    <div class="filter-card mb-4">
        <div class="filter-header" id="toggle_filter">
            <div class="filter-header-left">
                <div class="filter-icon">
                    <i class="ri-filter-3-line"></i>
                </div>
                <div class="filter-header-text">
                    <span class="filter-title">Search & Filter</span>
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
                    <select id="tenderid" class="modern-select select2-ajax"></select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-building-line"></i>
                        Ministry
                    </label>
                    <select id="ministry_division" class="modern-select select2">
                        <option value="">All Ministry</option>
                        @foreach($ministries as $ministry)
                            <option value="{{ $ministry }}">{{ $ministry }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-store-line"></i>
                        Supplier
                    </label>
                    <select id="supplier_name" class="modern-select select2-ajax-supplier"></select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-map-pin-line"></i>
                        District
                    </label>
                    <select id="district" class="modern-select select2">
                        <option value="">All Districts</option>
                        @foreach($districts as $district)
                            <option value="{{ $district }}">{{ $district }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-settings-3-line"></i>
                        Method
                    </label>
                    <select id="procurement_method" class="modern-select select2">
                        <option value="">All Methods</option>
                        @foreach($methods as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-calendar-line"></i>
                        From Date
                    </label>
                    <div class="input-wrapper">
                        <input type="date" id="from_date" class="modern-input">
                    </div>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-calendar-line"></i>
                        To Date
                    </label>
                    <div class="input-wrapper">
                        <input type="date" id="to_date" class="modern-input">
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button type="button" id="reset_button" class="filter-btn filter-btn-secondary">
                    <i class="ri-refresh-line"></i>
                    Reset
                </button>
                <button type="button" id="filter_button" class="filter-btn filter-btn-primary">
                    <i class="ri-search-line"></i>
                    Search
                </button>
            </div>
        </div>
    </div>

    <!-- Results Area -->
    <div id="results_area">
        <div class="results-header mb-3">
            <div class="results-info">
                <span id="results_count" class="results-count">Loading...</span>
            </div>
            <div class="results-pagination" id="pagination_top"></div>
        </div>

        <div class="tender-cards-grid" id="tender_cards">
            <!-- Cards will be loaded here -->
        </div>

        <div class="results-footer mt-4">
            <div class="results-pagination" id="pagination_bottom"></div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading_overlay">
        <div class="loading-spinner">
            <div class="fluent-loader-ring"></div>
            <div class="fluent-loader-ring"></div>
            <div class="fluent-loader-ring"></div>
        </div>
        <p>Loading tenders...</p>
    </div>
</div>

<!-- ================================== -->
<!-- TAB 2: TENDER ITEMS -->
<!-- ================================== -->
<div id="items_tab" class="tab-content">
    <!-- Filter Card -->
    <div class="filter-card mb-4">
        <div class="filter-header" id="toggle_item_filter">
            <div class="filter-header-left">
                <div class="filter-icon" style="background: linear-gradient(135deg, #00B294 0%, #54D6C6 100%);">
                    <i class="ri-filter-3-line"></i>
                </div>
                <div class="filter-header-text">
                    <span class="filter-title">Search / Filter Items</span>
                    <span class="filter-subtitle">Click to expand filters</span>
                </div>
            </div>
            <div class="filter-header-right">
                <span class="filter-badge" id="item_active_filters">No filters</span>
                <div class="filter-toggle-icon">
                    <i class="ri-arrow-down-s-line"></i>
                </div>
            </div>
        </div>
        <div class="filter-body" id="item_filter_body" style="display: none;">
            <div class="filter-grid">
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-hashtag"></i>
                        Tender ID
                    </label>
                    <select id="item_tender_id_filter" class="modern-select"></select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">
                        <i class="ri-map-2-line"></i>
                        Division
                    </label>
                    <select id="item_division_id" class="modern-select">
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
                    <select id="item_supplier_name" class="modern-select"></select>
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
                <button id="item_reset_button" class="filter-btn filter-btn-secondary">
                    <i class="ri-refresh-line"></i>
                    Reset
                </button>
                <button id="item_filter_button" class="filter-btn filter-btn-primary">
                    <i class="ri-search-line"></i>
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Results Header -->
    <div class="results-header mb-3">
        <div class="results-info">
            <span id="item_results_count" class="results-count">Loading...</span>
        </div>
        <div class="results-pagination" id="item_pagination_top"></div>
    </div>

    <!-- Loading State -->
    <div id="item_loading_state" class="loading-card" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner">
                <i class="ri-loader-4-line"></i>
            </div>
            <p>Loading tender items...</p>
        </div>
    </div>

    <!-- Empty State -->
    <div id="item_empty_state" class="empty-card" style="display: none;">
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
        <div class="results-pagination" id="item_pagination_bottom"></div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal-overlay" id="view_modal">
    <div class="modal-container modal-xl">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon">
                    <i class="ri-file-list-3-line"></i>
                </div>
                <div class="modal-header-text">
                    <h2 class="modal-title">Tender Details</h2>
                    <span class="modal-subtitle" id="view_tender_id"></span>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-body" id="view_modal_body">
            <!-- Content loaded dynamically -->
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-btn modal-btn-secondary" onclick="closeViewModal()">
                <i class="ri-close-line"></i> Close
            </button>
            <button type="button" class="modal-btn modal-btn-primary" id="view_items_btn">
                <i class="ri-list-check"></i> View Items
            </button>
        </div>
    </div>
</div>

<!-- Items Modal -->
<div class="modal-overlay" id="items_modal">
    <div class="modal-container modal-xxl">
        <div class="modal-header">
            <div class="modal-header-content">
                <div class="modal-icon items">
                    <i class="ri-list-check"></i>
                </div>
                <div class="modal-header-text">
                    <h2 class="modal-title">Bill of Quantities (BOQ)</h2>
                    <span class="modal-subtitle" id="items_tender_id"></span>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeItemsModal()">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-body" id="items_modal_body">
            <!-- Content loaded dynamically -->
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-btn modal-btn-secondary" onclick="closeItemsModal()">
                <i class="ri-close-line"></i> Close
            </button>
        </div>
    </div>
</div>

<!-- Detail Modal for Tender Items -->
<div class="modal-overlay" id="detail_modal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-header-left">
                <div class="modal-icon" style="background: linear-gradient(135deg, #00B294 0%, #54D6C6 100%);">
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
/* Tab Navigation */
.tender-tabs {
    display: flex;
    gap: 8px;
    background: var(--fluent-bg-primary);
    padding: 8px;
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
}
.tender-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 24px;
    border: none;
    background: transparent;
    border-radius: var(--fluent-radius-md);
    font-size: 14px;
    font-weight: 500;
    color: var(--fluent-text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
}
.tender-tab:hover {
    background: var(--fluent-gray-20);
    color: var(--fluent-text-primary);
}
.tender-tab.active {
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    color: white;
    box-shadow: var(--fluent-shadow-8);
}
.tender-tab i {
    font-size: 18px;
}

/* Tab Content */
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}

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
.input-wrapper {
    position: relative;
}
.modern-select, .modern-input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    font-size: 14px;
    transition: all 0.15s ease;
}
.modern-select:hover, .modern-input:hover {
    border-color: var(--fluent-gray-50);
}
.modern-select:focus, .modern-input:focus {
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

/* Tender Cards Grid */
.tender-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 20px;
}

/* Tender Card */
.tender-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}
.tender-card:hover {
    box-shadow: var(--fluent-shadow-16);
    transform: translateY(-2px);
}

.tender-card-header {
    padding: 16px 20px;
    padding-right: 100px;
    border-bottom: 1px solid var(--fluent-gray-30);
    background: var(--fluent-gray-20);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}
.tender-id-badge {
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    color: white;
    padding: 6px 12px;
    border-radius: var(--fluent-radius-md);
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}
.tender-value {
    background: var(--fluent-success-light);
    color: #0B5A08;
    padding: 6px 12px;
    border-radius: var(--fluent-radius-md);
    font-size: 14px;
    font-weight: 700;
    font-family: 'Consolas', monospace;
}

.tender-card-body {
    padding: 16px 20px;
}
.tender-info-row {
    display: flex;
    margin-bottom: 12px;
    gap: 8px;
}
.tender-info-row:last-child {
    margin-bottom: 0;
}
.tender-info-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: var(--fluent-text-tertiary);
    min-width: 80px;
    flex-shrink: 0;
}
.tender-info-value {
    font-size: 13px;
    color: var(--fluent-text-primary);
    line-height: 1.4;
}
.tender-info-value.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tender-method-badge {
    display: inline-block;
    background: var(--fluent-info-light);
    color: #004578;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

/* Card Actions */
.tender-card-actions {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    gap: 6px;
    z-index: 10;
}
.tender-action-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: var(--fluent-text-secondary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.tender-action-icon:hover {
    transform: scale(1.1);
}
.tender-action-icon.view {
    color: var(--fluent-primary);
}
.tender-action-icon.view:hover {
    background: var(--fluent-primary);
    color: white;
}
.tender-action-icon.items {
    color: #107C10;
}
.tender-action-icon.items:hover {
    background: #107C10;
    color: white;
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

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.9);
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.loading-overlay.active {
    display: flex;
}
.loading-spinner {
    position: relative;
    width: 60px;
    height: 60px;
}
.loading-overlay p {
    margin-top: 16px;
    color: var(--fluent-text-secondary);
}

/* Loading Card for Items */
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
.loading-content .loading-spinner i {
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    grid-column: 1 / -1;
}
.empty-state i {
    font-size: 64px;
    color: var(--fluent-gray-40);
}
.empty-state h3 {
    margin: 16px 0 8px;
    color: var(--fluent-text-primary);
}
.empty-state p {
    color: var(--fluent-text-secondary);
    margin: 0;
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
    cursor: pointer;
}
.item-card:hover {
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
    transform: translateY(-4px);
    border-color: #00B294;
}
.card-top {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--fluent-gray-20);
}
.card-badge {
    background: linear-gradient(135deg, #00B294 0%, #54D6C6 100%);
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
    color: #00B294;
    font-size: 16px;
}
.card-supplier span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
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
    background: linear-gradient(135deg, rgba(0, 178, 148, 0.08) 0%, rgba(84, 214, 198, 0.08) 100%);
}
.metric.highlight .metric-value {
    color: #00B294;
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

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.modal-overlay.active, .modal-overlay.show {
    display: flex;
    opacity: 1;
}
.modal-container {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-xl);
    box-shadow: var(--fluent-shadow-64);
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    transform: scale(0.95) translateY(20px);
    transition: transform 0.25s ease;
}
.modal-overlay.active .modal-container, .modal-overlay.show .modal-container {
    transform: scale(1) translateY(0);
}
.modal-container.modal-xl {
    max-width: 900px;
}
.modal-container.modal-xxl {
    max-width: 1200px;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid var(--fluent-gray-20);
    background: var(--fluent-gray-10);
    border-radius: var(--fluent-radius-xl) var(--fluent-radius-xl) 0 0;
}
.modal-header-content, .modal-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.modal-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
}
.modal-icon.items {
    background: linear-gradient(135deg, #107C10 0%, #54B054 100%);
}
.modal-header-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
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
}
.modal-close {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    border-radius: var(--fluent-radius-md);
    cursor: pointer;
    color: var(--fluent-text-secondary);
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
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
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid var(--fluent-gray-20);
    background: var(--fluent-gray-10);
    border-radius: 0 0 var(--fluent-radius-xl) var(--fluent-radius-xl);
}
.modal-btn {
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
.modal-btn-primary {
    background: var(--fluent-primary);
    color: white;
}
.modal-btn-primary:hover {
    background: #106EBE;
}
.modal-btn-secondary {
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    border: 1px solid var(--fluent-gray-30);
}
.modal-btn-secondary:hover {
    background: var(--fluent-gray-20);
}

/* View Modal Content */
.detail-sections-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}
@media (max-width: 768px) {
    .detail-sections-grid {
        grid-template-columns: 1fr;
    }
}
.detail-section {
    background: var(--fluent-gray-10);
    border-radius: var(--fluent-radius-lg);
    padding: 20px;
    border: 1px solid var(--fluent-gray-20);
}
.detail-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--fluent-gray-20);
}
.detail-section-icon {
    width: 32px;
    height: 32px;
    background: var(--fluent-primary);
    border-radius: var(--fluent-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}
.detail-section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    margin: 0;
}
.detail-rows {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}
.detail-row-label {
    font-size: 12px;
    color: var(--fluent-text-secondary);
    min-width: 100px;
    flex-shrink: 0;
}
.detail-row-value {
    font-size: 13px;
    color: var(--fluent-text-primary);
    text-align: right;
    word-break: break-word;
}
.detail-row-value.highlight {
    background: var(--fluent-success-light);
    color: #0B5A08;
    padding: 6px 12px;
    border-radius: var(--fluent-radius-md);
    font-weight: 600;
}

/* Items Modal Content */
.items-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    padding: 20px;
    background: var(--fluent-gray-10);
    border-radius: var(--fluent-radius-lg);
    border: 1px solid var(--fluent-gray-20);
}
.items-info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.items-info-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fluent-text-tertiary);
}
.items-info-value {
    font-size: 14px;
    color: var(--fluent-text-primary);
    font-weight: 500;
    word-break: break-word;
    overflow-wrap: break-word;
    line-height: 1.4;
}
.items-summary {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
}
.items-summary-card {
    flex: 1;
    padding: 16px 20px;
    border-radius: var(--fluent-radius-lg);
    display: flex;
    align-items: center;
    gap: 12px;
}
.items-summary-card.count {
    background: linear-gradient(135deg, rgba(0, 120, 212, 0.1) 0%, rgba(0, 188, 242, 0.1) 100%);
    border: 1px solid rgba(0, 120, 212, 0.2);
}
.items-summary-card.total {
    background: linear-gradient(135deg, rgba(16, 124, 16, 0.1) 0%, rgba(84, 176, 84, 0.1) 100%);
    border: 1px solid rgba(16, 124, 16, 0.2);
}
.items-summary-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.items-summary-card.count .items-summary-icon {
    background: var(--fluent-primary);
    color: white;
}
.items-summary-card.total .items-summary-icon {
    background: #107C10;
    color: white;
}
.items-summary-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.items-summary-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--fluent-text-primary);
    font-family: 'Consolas', monospace;
}
.items-summary-label {
    font-size: 12px;
    color: var(--fluent-text-secondary);
}
.items-table-wrapper {
    border: 1px solid var(--fluent-gray-20);
    border-radius: var(--fluent-radius-lg);
    overflow: hidden;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
}
.items-table thead {
    background: var(--fluent-gray-20);
}
.items-table th {
    padding: 14px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: var(--fluent-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.items-table td {
    padding: 14px 16px;
    font-size: 13px;
    color: var(--fluent-text-primary);
    border-bottom: 1px solid var(--fluent-gray-20);
    word-break: break-word;
    overflow-wrap: break-word;
}
.items-table tbody tr:last-child td {
    border-bottom: none;
}
.items-table tbody tr:hover {
    background: var(--fluent-gray-10);
}
.items-table .text-right {
    text-align: right;
}
.items-table .font-mono {
    font-family: 'Consolas', monospace;
}
.items-table tfoot {
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
}
.items-table tfoot th {
    color: white;
    font-size: 14px;
    padding: 16px;
}
.item-code-badge {
    background: var(--fluent-gray-20);
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Consolas', monospace;
    font-size: 12px;
}
.item-unit-badge {
    background: var(--fluent-info-light);
    color: #004578;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}
.modal-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: var(--fluent-text-secondary);
}
.modal-loading i {
    font-size: 48px;
    margin-bottom: 16px;
    animation: spin 1s linear infinite;
}

/* Item Detail Modal Styles */
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
    color: #00B294;
    font-weight: 600;
}
.amount-card {
    background: linear-gradient(135deg, rgba(0, 178, 148, 0.08) 0%, rgba(84, 214, 198, 0.08) 100%);
    border: 1px solid rgba(0, 178, 148, 0.2);
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
    color: #00B294;
    font-family: 'Consolas', monospace;
}
.amount-suffix {
    font-size: 14px;
    color: var(--fluent-text-secondary);
    margin-left: 4px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .tender-tabs {
        flex-direction: column;
    }
    .tender-cards-grid {
        grid-template-columns: 1fr;
    }
    .items-cards-grid {
        grid-template-columns: 1fr;
    }
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .detail-item.full-width {
        grid-column: span 1;
    }
    .amount-value {
        font-size: 22px;
    }
}
</style>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    // ================================
    // TAB SWITCHING
    // ================================
    let itemsLoaded = false;

    $('.tender-tab').on('click', function() {
        const tab = $(this).data('tab');

        // Update tab buttons
        $('.tender-tab').removeClass('active');
        $(this).addClass('active');

        // Update tab content
        $('.tab-content').removeClass('active');
        if (tab === 'tenders') {
            $('#tenders_tab').addClass('active');
        } else {
            $('#items_tab').addClass('active');
            if (!itemsLoaded) {
                loadItemsData(1);
                itemsLoaded = true;
            }
        }
    });

    // ================================
    // TENDERS TAB FUNCTIONALITY
    // ================================
    let currentPage = 1;
    let totalPages = 1;
    const perPage = 12;

    // Initialize Select2
    $('.select2').select2({ width: '100%', allowClear: true });

    $('.select2-ajax').select2({
        placeholder: "Type Tender ID...",
        minimumInputLength: 1,
        allowClear: true,
        width: '100%',
        ajax: {
            url: "/admin/tender-select-search",
            dataType: 'json',
            delay: 250,
            processResults: data => ({ results: data }),
            cache: true
        }
    });

    $('.select2-ajax-supplier').select2({
        placeholder: "Type Supplier Name...",
        minimumInputLength: 2,
        allowClear: true,
        width: '100%',
        ajax: {
            url: "/admin/tender-supplier-search",
            dataType: 'json',
            delay: 250,
            processResults: data => ({ results: data }),
            cache: true
        }
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
        if ($('#tenderid').val()) count++;
        if ($('#ministry_division').val()) count++;
        if ($('#supplier_name').val()) count++;
        if ($('#district').val()) count++;
        if ($('#procurement_method').val()) count++;
        if ($('#from_date').val()) count++;
        if ($('#to_date').val()) count++;

        const $badge = $('#active_filters');
        if (count > 0) {
            $badge.text(count + ' filter' + (count > 1 ? 's' : '') + ' active');
            $badge.addClass('active');
        } else {
            $badge.text('No filters');
            $badge.removeClass('active');
        }
    }

    // Load data
    function loadTenders(page = 1) {
        currentPage = page;
        $('#loading_overlay').addClass('active');

        $.ajax({
            url: "{{ route('admin.tender.index') }}",
            data: {
                page: page,
                per_page: perPage,
                tenderid: $('#tenderid').val(),
                ministry_division: $('#ministry_division').val(),
                supplier_name: $('#supplier_name').val(),
                district: $('#district').val(),
                procurement_method: $('#procurement_method').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val()
            },
            success: function(response) {
                renderCards(response.data);
                renderPagination(response);
                $('#results_count').html(`Showing <strong>${response.from || 0}-${response.to || 0}</strong> of <strong>${response.total}</strong> tenders`);
                $('#loading_overlay').removeClass('active');
            },
            error: function() {
                $('#tender_cards').html('<div class="empty-state"><i class="ri-error-warning-line"></i><h3>Error Loading Data</h3><p>Please try again later.</p></div>');
                $('#loading_overlay').removeClass('active');
            }
        });
    }

    function renderCards(tenders) {
        if (!tenders || tenders.length === 0) {
            $('#tender_cards').html('<div class="empty-state"><i class="ri-inbox-line"></i><h3>No Tenders Found</h3><p>Try adjusting your filters.</p></div>');
            return;
        }

        let html = '';
        tenders.forEach(tender => {
            const valueCr = (tender.contract_value / 10000000).toFixed(2);
            const notifDate = tender.date_notification_award ? new Date(tender.date_notification_award).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'}) : 'N/A';

            html += `
                <div class="tender-card" onclick="openViewModal(${tender.id})">
                    <div class="tender-card-actions">
                        <button type="button" class="tender-action-icon view" onclick="event.stopPropagation(); openViewModal(${tender.id})" title="View Details">
                            <i class="ri-eye-line"></i>
                        </button>
                        <button type="button" class="tender-action-icon items" onclick="event.stopPropagation(); openItemsModal(${tender.id})" title="View Items">
                            <i class="ri-list-check"></i>
                        </button>
                    </div>
                    <div class="tender-card-header">
                        <span class="tender-id-badge">${tender.tenderid}</span>
                        <span class="tender-value">${valueCr} Cr</span>
                    </div>
                    <div class="tender-card-body">
                        <div class="tender-info-row">
                            <span class="tender-info-label">Ministry</span>
                            <span class="tender-info-value truncate" title="${tender.ministry_division || ''}">${tender.ministry_division || 'N/A'}</span>
                        </div>
                        <div class="tender-info-row">
                            <span class="tender-info-label">Entity</span>
                            <span class="tender-info-value truncate" title="${tender.procuring_entity_name || ''}">${tender.procuring_entity_name || 'N/A'}</span>
                        </div>
                        <div class="tender-info-row">
                            <span class="tender-info-label">District</span>
                            <span class="tender-info-value">${tender.procuring_entity_district || 'N/A'}</span>
                        </div>
                        <div class="tender-info-row">
                            <span class="tender-info-label">Method</span>
                            <span class="tender-info-value"><span class="tender-method-badge">${tender.procurement_method || 'N/A'}</span></span>
                        </div>
                        <div class="tender-info-row">
                            <span class="tender-info-label">Supplier</span>
                            <span class="tender-info-value truncate" title="${tender.supplier_name || ''}">${tender.supplier_name || 'N/A'}</span>
                        </div>
                        <div class="tender-info-row">
                            <span class="tender-info-label">Date</span>
                            <span class="tender-info-value">${notifDate}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#tender_cards').html(html);
    }

    function renderPagination(response) {
        totalPages = response.last_page;
        let html = '<div class="pagination-container">';

        html += `<button class="page-btn" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}><i class="ri-arrow-left-s-line"></i></button>`;

        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += `<button class="page-btn" onclick="goToPage(1)">1</button>`;
            if (startPage > 2) html += `<span style="padding: 0 8px;">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span style="padding: 0 8px;">...</span>`;
            html += `<button class="page-btn" onclick="goToPage(${totalPages})">${totalPages}</button>`;
        }

        html += `<button class="page-btn" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}><i class="ri-arrow-right-s-line"></i></button>`;
        html += '</div>';

        $('#pagination_top, #pagination_bottom').html(html);
    }

    window.goToPage = function(page) {
        if (page >= 1 && page <= totalPages) {
            loadTenders(page);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    $('#filter_button').click(() => {
        loadTenders(1);
        updateActiveFilters();
    });
    $('#reset_button').click(() => {
        $('#tenderid, #ministry_division, #supplier_name, #district, #procurement_method').val('').trigger('change');
        $('#from_date, #to_date').val('');
        loadTenders(1);
        updateActiveFilters();
    });

    // Initial load
    loadTenders(1);

    // Store current tender ID for modal actions
    let currentTenderId = null;

    // View Details Modal
    window.openViewModal = function(id) {
        currentTenderId = id;
        $('#view_modal_body').html('<div class="modal-loading"><i class="ri-loader-4-line"></i><p>Loading tender details...</p></div>');
        $('#view_modal').addClass('active');
        document.body.style.overflow = 'hidden';

        $.ajax({
            url: `/admin/tender/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    renderViewModal(response.tender);
                }
            },
            error: function() {
                $('#view_modal_body').html('<div class="modal-loading"><i class="ri-error-warning-line" style="animation:none;color:var(--fluent-danger);"></i><p>Error loading tender details</p></div>');
            }
        });
    };

    window.closeViewModal = function() {
        $('#view_modal').removeClass('active');
        document.body.style.overflow = '';
    };

    function renderViewModal(tender) {
        const formatDate = (dateStr) => {
            if (!dateStr) return 'N/A';
            return new Date(dateStr).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'});
        };
        const formatCurrency = (value) => {
            return parseFloat(value || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        };

        $('#view_tender_id').text('Tender ID: ' + tender.tenderid);

        let html = `
            <div class="detail-sections-grid">
                <div class="detail-section">
                    <div class="detail-section-header">
                        <div class="detail-section-icon"><i class="ri-information-line"></i></div>
                        <h4 class="detail-section-title">Basic Information</h4>
                    </div>
                    <div class="detail-rows">
                        <div class="detail-row">
                            <span class="detail-row-label">Tender ID</span>
                            <span class="detail-row-value"><span class="item-code-badge">${tender.tenderid}</span></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Ministry / Division</span>
                            <span class="detail-row-value">${tender.ministry_division || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Agency</span>
                            <span class="detail-row-value">${tender.agency || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Project Name</span>
                            <span class="detail-row-value">${tender.project_name || 'N/A'}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-header">
                        <div class="detail-section-icon" style="background:#7719AA;"><i class="ri-archive-line"></i></div>
                        <h4 class="detail-section-title">Package & Procurement</h4>
                    </div>
                    <div class="detail-rows">
                        <div class="detail-row">
                            <span class="detail-row-label">Procuring Entity</span>
                            <span class="detail-row-value">${tender.procuring_entity_name || 'N/A'} <code>${tender.procuring_entity_code || ''}</code></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">District</span>
                            <span class="detail-row-value">${tender.procuring_entity_district || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Procurement Method</span>
                            <span class="detail-row-value"><span class="item-unit-badge">${tender.procurement_method || 'N/A'}</span></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Package No</span>
                            <span class="detail-row-value"><strong>${tender.tender_package_no || 'N/A'}</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Package Name</span>
                            <span class="detail-row-value">${tender.tender_package_name || 'N/A'}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-header">
                        <div class="detail-section-icon" style="background:#107C10;"><i class="ri-handshake-line"></i></div>
                        <h4 class="detail-section-title">Contract & Award</h4>
                    </div>
                    <div class="detail-rows">
                        <div class="detail-row">
                            <span class="detail-row-label">Supplier Name</span>
                            <span class="detail-row-value"><strong>${tender.supplier_name || 'N/A'}</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Contract Value</span>
                            <span class="detail-row-value highlight">${formatCurrency(tender.contract_value)} BDT</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Notification Date</span>
                            <span class="detail-row-value">${formatDate(tender.date_notification_award)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Contract Signing</span>
                            <span class="detail-row-value">${formatDate(tender.date_contract_signing)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Proposed Completion</span>
                            <span class="detail-row-value">${formatDate(tender.proposed_date_contract_completion)}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-header">
                        <div class="detail-section-icon" style="background:#D83B01;"><i class="ri-file-info-line"></i></div>
                        <h4 class="detail-section-title">Other Details</h4>
                    </div>
                    <div class="detail-rows">
                        <div class="detail-row">
                            <span class="detail-row-label">Supplier Location</span>
                            <span class="detail-row-value">${tender.supplier_location || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Budget Source</span>
                            <span class="detail-row-value">${tender.budget_source_funds || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Delivery Location</span>
                            <span class="detail-row-value">${tender.delivery_location || 'N/A'}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-row-label">Total Items</span>
                            <span class="detail-row-value"><strong>${tender.items ? tender.items.length : 0}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#view_modal_body').html(html);
    }

    // View Items button in View Modal
    $('#view_items_btn').on('click', function() {
        closeViewModal();
        if (currentTenderId) {
            openItemsModal(currentTenderId);
        }
    });

    // Items Modal
    window.openItemsModal = function(id) {
        currentTenderId = id;
        $('#items_modal_body').html('<div class="modal-loading"><i class="ri-loader-4-line"></i><p>Loading items...</p></div>');
        $('#items_modal').addClass('active');
        document.body.style.overflow = 'hidden';

        $.ajax({
            url: `/admin/tender/${id}/items`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    renderItemsModalContent(response.tender, response.items || [], response.totalAmount);
                } else {
                    $('#items_modal_body').html('<div class="modal-loading"><i class="ri-error-warning-line" style="animation:none;color:var(--fluent-warning);"></i><p>No data returned</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Items load error:', error, xhr.responseText);
                $('#items_modal_body').html('<div class="modal-loading"><i class="ri-error-warning-line" style="animation:none;color:var(--fluent-danger);"></i><p>Error loading items</p></div>');
            }
        });
    };

    window.closeItemsModal = function() {
        $('#items_modal').removeClass('active');
        document.body.style.overflow = '';
    };

    function renderItemsModalContent(tender, items, totalAmount) {
        const formatCurrency = (value) => {
            return parseFloat(value || 0).toLocaleString('en-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        };

        $('#items_tender_id').text('Tender ID: ' + (tender.tenderid || tender.id || 'N/A'));

        let itemsHtml = '';
        let grandTotal = 0;

        // Ensure items is an array
        const itemsArray = Array.isArray(items) ? items : [];

        itemsArray.forEach(item => {
            const lineTotal = parseFloat(item.item_quantity) * parseFloat(item.item_rate);
            grandTotal += lineTotal;
            itemsHtml += `
                <tr>
                    <td><span class="item-code-badge">${item.item_code || 'N/A'}</span></td>
                    <td>${item.item_name || 'N/A'}</td>
                    <td><span class="item-unit-badge">${item.item_unit || 'N/A'}</span></td>
                    <td class="text-right font-mono">${formatCurrency(item.item_quantity)}</td>
                    <td class="text-right font-mono">${formatCurrency(item.item_rate)}</td>
                    <td class="text-right font-mono"><strong>${formatCurrency(lineTotal)}</strong></td>
                </tr>
            `;
        });

        let html = `
            <div class="items-info-grid">
                <div class="items-info-item">
                    <span class="items-info-label"><i class="ri-building-line"></i> Ministry / Division</span>
                    <span class="items-info-value">${tender.ministry_division || 'N/A'}</span>
                </div>
                <div class="items-info-item">
                    <span class="items-info-label"><i class="ri-government-line"></i> Procuring Entity</span>
                    <span class="items-info-value">${tender.procuring_entity_name || 'N/A'}</span>
                </div>
                <div class="items-info-item">
                    <span class="items-info-label"><i class="ri-map-pin-line"></i> District</span>
                    <span class="items-info-value">${tender.procuring_entity_district || 'N/A'}</span>
                </div>
                <div class="items-info-item">
                    <span class="items-info-label"><i class="ri-hashtag"></i> Package No</span>
                    <span class="items-info-value"><strong>${tender.tender_package_no || 'N/A'}</strong></span>
                </div>
            </div>

            <div class="items-summary">
                <div class="items-summary-card count">
                    <div class="items-summary-icon"><i class="ri-stack-line"></i></div>
                    <div class="items-summary-content">
                        <span class="items-summary-value">${itemsArray.length}</span>
                        <span class="items-summary-label">Total Items</span>
                    </div>
                </div>
                <div class="items-summary-card total">
                    <div class="items-summary-icon"><i class="ri-money-dollar-circle-line"></i></div>
                    <div class="items-summary-content">
                        <span class="items-summary-value">${formatCurrency(grandTotal)}</span>
                        <span class="items-summary-label">Total Amount (BDT)</span>
                    </div>
                </div>
            </div>

            <div class="items-table-wrapper">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:100px;">Item Code</th>
                            <th>Description of Items</th>
                            <th style="width:80px;">Unit</th>
                            <th style="width:110px;" class="text-right">Quantity</th>
                            <th style="width:120px;" class="text-right">Rate (BDT)</th>
                            <th style="width:140px;" class="text-right">Total (BDT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml || '<tr><td colspan="6" class="text-center" style="padding:40px 20px;"><div style="color:var(--fluent-text-secondary);"><i class="ri-inbox-line" style="font-size:32px;display:block;margin-bottom:8px;opacity:0.5;"></i>No items found for this tender</div></td></tr>'}
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Total Estimated Amount:</th>
                            <th class="text-right font-mono">${formatCurrency(grandTotal)}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        $('#items_modal_body').html(html);
    }

    // ================================
    // TENDER ITEMS TAB FUNCTIONALITY
    // ================================
    let itemCurrentPage = 1;
    const itemPerPage = 12;

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

    select2Ajax('#item_tender_id_filter', '/admin/tender-select-search', 'Type Tender ID...');
    select2Ajax('#item_supplier_name', '/admin/supplier-search', 'Type Supplier Name...');
    select2Ajax('#item_code', '/admin/item-code-search', 'Type Item Code...');
    select2Ajax('#item_name', '/admin/item-name-search', 'Type Item Name...');

    $('#item_division_id').select2({
        width: '100%',
        placeholder: 'Select Division',
        allowClear: true
    });

    // Item Filter toggle functionality
    $('#toggle_item_filter').on('click', function() {
        const $filterBody = $('#item_filter_body');
        const $header = $(this);
        const isExpanded = $filterBody.is(':visible');

        $filterBody.slideToggle(250);
        $header.toggleClass('expanded', !isExpanded);

        const subtitle = $header.find('.filter-subtitle');
        subtitle.text(isExpanded ? 'Click to expand filters' : 'Click to collapse filters');
    });

    // Update active filters badge for items
    function updateItemActiveFilters() {
        let count = 0;
        if ($('#item_tender_id_filter').val()) count++;
        if ($('#item_division_id').val()) count++;
        if ($('#item_supplier_name').val()) count++;
        if ($('#item_code').val()) count++;
        if ($('#item_name').val()) count++;

        const $badge = $('#item_active_filters');
        if (count > 0) {
            $badge.text(count + ' filter' + (count > 1 ? 's' : '') + ' active');
            $badge.addClass('active');
        } else {
            $badge.text('No filters');
            $badge.removeClass('active');
        }
    }

    function loadItemsData(page = 1) {
        itemCurrentPage = page;
        $('#item_loading_state').show();
        $('#items_grid').hide();
        $('#item_empty_state').hide();
        $('#item_pagination_top, #item_pagination_bottom').hide();

        $.ajax({
            url: "{{ route('admin.tender-item.index') }}",
            type: 'GET',
            data: {
                page: page,
                per_page: itemPerPage,
                tender_id_filter: $('#item_tender_id_filter').val(),
                division_id: $('#item_division_id').val(),
                supplier_name: $('#item_supplier_name').val(),
                item_code: $('#item_code').val(),
                item_name: $('#item_name').val()
            },
            success: function(response) {
                $('#item_loading_state').hide();

                if (response.data && response.data.length > 0) {
                    renderItemCards(response.data);
                    renderItemPagination(response);
                    $('#item_results_count').html(`Showing <strong>${response.from || 0}-${response.to || 0}</strong> of <strong>${response.total}</strong> items`);
                    $('#items_grid').show();
                    $('#item_pagination_top, #item_pagination_bottom').show();
                } else {
                    $('#item_empty_state').show();
                    $('#item_results_count').html('No items found');
                }
            },
            error: function() {
                $('#item_loading_state').hide();
                $('#item_empty_state').show();
                $('#item_results_count').html('Error loading items');
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

    function renderItemPagination(response) {
        const { current_page, last_page } = response;
        let html = '<div class="pagination-container">';

        html += `<button class="page-btn" onclick="goToItemPage(${current_page - 1})" ${current_page === 1 ? 'disabled' : ''}><i class="ri-arrow-left-s-line"></i></button>`;

        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        if (startPage > 1) {
            html += `<button class="page-btn" onclick="goToItemPage(1)">1</button>`;
            if (startPage > 2) html += `<span style="padding: 0 8px;">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="page-btn ${i === current_page ? 'active' : ''}" onclick="goToItemPage(${i})">${i}</button>`;
        }

        if (endPage < last_page) {
            if (endPage < last_page - 1) html += `<span style="padding: 0 8px;">...</span>`;
            html += `<button class="page-btn" onclick="goToItemPage(${last_page})">${last_page}</button>`;
        }

        html += `<button class="page-btn" onclick="goToItemPage(${current_page + 1})" ${current_page === last_page ? 'disabled' : ''}><i class="ri-arrow-right-s-line"></i></button>`;
        html += '</div>';

        $('#item_pagination_top, #item_pagination_bottom').html(html);
    }

    window.goToItemPage = function(page) {
        loadItemsData(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    $('#item_filter_button').on('click', function() {
        loadItemsData(1);
        updateItemActiveFilters();
    });

    $('#item_reset_button').on('click', function() {
        $('#item_tender_id_filter, #item_division_id, #item_supplier_name, #item_code, #item_name')
            .val(null).trigger('change');
        loadItemsData(1);
        updateItemActiveFilters();
    });

    // ================================
    // ITEM DETAIL MODAL
    // ================================
    window.openDetailModal = function(itemId) {
        const modal = document.getElementById('detail_modal');
        const modalBody = document.getElementById('modal_body');
        const modalItemCode = document.getElementById('modal_item_code');

        modal.classList.add('show');
        modalItemCode.textContent = 'Loading...';
        modalBody.innerHTML = `
            <div class="modal-loading">
                <i class="ri-loader-4-line"></i>
                <p>Loading item details...</p>
            </div>
        `;

        $.ajax({
            url: `/admin/tender-item/${itemId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.item) {
                    renderDetailModalContent(response.item);
                } else {
                    modalBody.innerHTML = '<p style="text-align: center; color: var(--fluent-text-secondary);">Failed to load item details</p>';
                }
            },
            error: function() {
                modalBody.innerHTML = '<p style="text-align: center; color: var(--fluent-text-secondary);">Error loading item details</p>';
            }
        });

        document.body.style.overflow = 'hidden';
    };

    window.closeDetailModal = function() {
        const modal = document.getElementById('detail_modal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    };

    function renderDetailModalContent(item) {
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
            <div class="detail-section" style="margin-bottom: 24px;">
                <div class="detail-section-title" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--fluent-text-tertiary); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--fluent-gray-20);">Item Information</div>
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

            <div class="detail-section" style="margin-bottom: 24px;">
                <div class="detail-section-title" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--fluent-text-tertiary); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--fluent-gray-20);">Tender Information</div>
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
                <div class="detail-section-title" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--fluent-text-tertiary); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--fluent-gray-20);">Pricing Details</div>
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

    // ================================
    // HELPER FUNCTIONS
    // ================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ================================
    // MODAL EVENT HANDLERS
    // ================================
    // Close modals on overlay click
    $('#view_modal, #items_modal, #detail_modal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('active').removeClass('show');
            document.body.style.overflow = '';
        }
    });

    // Close modals on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if ($('#detail_modal').hasClass('show')) {
                closeDetailModal();
            } else if ($('#items_modal').hasClass('active')) {
                closeItemsModal();
            } else if ($('#view_modal').hasClass('active')) {
                closeViewModal();
            }
        }
    });
});
</script>
@endsection
