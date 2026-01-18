@extends('layouts.admin')
@section('title', 'Stations - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-map-pin-line"></i>
        </span>
        {{ trans('cruds.station.title_singular') }} {{ trans('global.list') }}
    </h1>
    <div class="fluent-page-actions">
        @can('station_create')
            <button type="button" class="fluent-btn fluent-btn-primary" onclick="openCreateModal()">
                <i class="ri-add-line"></i>
                Add Station
            </button>
        @endcan
    </div>
</div>

<!-- Stats Summary -->
<div class="stats-row mb-4">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="ri-map-pin-2-line"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="stat_total">-</span>
            <span class="stat-label">Total Stations</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="ri-checkbox-circle-line"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="stat_active">-</span>
            <span class="stat-label">Active</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="ri-close-circle-line"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="stat_inactive">-</span>
            <span class="stat-label">Inactive</span>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar mb-3">
    <div class="search-box">
        <i class="ri-search-line search-icon"></i>
        <input type="text" id="search_input" class="fluent-input" placeholder="Search stations...">
    </div>
    <div class="filter-actions-inline">
        <select id="status_filter" class="fluent-select">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
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
<div id="loading_state" class="loading-card">
    <div class="loading-content">
        <div class="loading-spinner">
            <i class="ri-loader-4-line"></i>
        </div>
        <p>Loading stations...</p>
    </div>
</div>

<!-- Empty State -->
<div id="empty_state" class="empty-card" style="display: none;">
    <div class="empty-content">
        <div class="empty-icon">
            <i class="ri-map-pin-line"></i>
        </div>
        <h3>No Stations Found</h3>
        <p>No stations match your search criteria.</p>
    </div>
</div>

<!-- Station Cards Grid -->
<div class="cards-grid" id="stations_grid" style="display: none;"></div>

<!-- Results Footer -->
<div class="results-footer mt-4">
    <div class="results-pagination" id="pagination_bottom"></div>
</div>

<!-- View Modal -->
<div class="modal-overlay" id="viewModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-header-icon view">
                <i class="ri-map-pin-2-line"></i>
            </div>
            <div class="modal-header-content">
                <h2 class="modal-title">Station Details</h2>
                <p class="modal-subtitle" id="viewModalSubtitle">View station information</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('viewModal')">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="modal-body" id="viewModalContent">
            <div class="modal-loading">
                <i class="ri-loader-4-line"></i>
                <span>Loading...</span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="fluent-btn fluent-btn-secondary" onclick="closeModal('viewModal')">
                <i class="ri-close-line"></i>
                Close
            </button>
            @can('station_edit')
                <button type="button" class="fluent-btn fluent-btn-primary" id="viewToEditBtn">
                    <i class="ri-pencil-line"></i>
                    Edit Station
                </button>
            @endcan
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-header-icon edit" id="editModalIcon">
                <i class="ri-pencil-line"></i>
            </div>
            <div class="modal-header-content">
                <h2 class="modal-title" id="editModalTitle">Edit Station</h2>
                <p class="modal-subtitle" id="editModalSubtitle">Update station information</p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('editModal')">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <form id="stationForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-body">
                <div class="fluent-form-group">
                    <label class="fluent-label fluent-label-required" for="modal_station_name">
                        <i class="ri-map-pin-line"></i>
                        Station Name
                    </label>
                    <input type="text" class="fluent-input" name="station_name" id="modal_station_name" required placeholder="Enter station name">
                    <div class="fluent-invalid-feedback" id="error_station_name"></div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label" for="modal_lat">
                                <i class="ri-compass-3-line"></i>
                                Latitude
                            </label>
                            <input type="number" class="fluent-input" name="lat" id="modal_lat" step="0.00001" placeholder="e.g., 23.8103">
                            <div class="fluent-invalid-feedback" id="error_lat"></div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label" for="modal_lon">
                                <i class="ri-compass-line"></i>
                                Longitude
                            </label>
                            <input type="number" class="fluent-input" name="lon" id="modal_lon" step="0.00001" placeholder="e.g., 90.4125">
                            <div class="fluent-invalid-feedback" id="error_lon"></div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label" for="modal_elevation">
                                <i class="ri-arrow-up-line"></i>
                                Elevation (m)
                            </label>
                            <input type="number" class="fluent-input" name="elevation" id="modal_elevation" step="0.00001" placeholder="e.g., 4.0">
                            <div class="fluent-invalid-feedback" id="error_elevation"></div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="fluent-form-group">
                            <label class="fluent-label">
                                <i class="ri-checkbox-circle-line"></i>
                                Status
                            </label>
                            <div class="status-toggle-group">
                                <label class="status-toggle">
                                    <input type="radio" name="status" value="1" checked>
                                    <span class="status-toggle-btn active">
                                        <i class="ri-checkbox-circle-line"></i>
                                        Active
                                    </span>
                                </label>
                                <label class="status-toggle">
                                    <input type="radio" name="status" value="0">
                                    <span class="status-toggle-btn inactive">
                                        <i class="ri-close-circle-line"></i>
                                        Inactive
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="fluent-btn fluent-btn-secondary" onclick="closeModal('editModal')">
                    <i class="ri-close-line"></i>
                    Cancel
                </button>
                <button type="submit" class="fluent-btn fluent-btn-primary" id="submitBtn">
                    <i class="ri-save-line"></i>
                    <span id="submitBtnText">Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
/* Filter Bar */
.filter-bar {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}
.search-box {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 300px;
}
.search-box .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--fluent-text-tertiary);
}
.search-box .fluent-input {
    padding-left: 36px;
}
.fluent-select {
    padding: 10px 14px;
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    background: var(--fluent-bg-primary);
    color: var(--fluent-text-primary);
    font-size: 14px;
    min-width: 140px;
}

/* Stats Row */
.stats-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--fluent-bg-primary);
    padding: 20px 24px;
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
}
.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: var(--fluent-radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.stat-icon.primary {
    background: linear-gradient(135deg, rgba(0, 120, 212, 0.15) 0%, rgba(0, 188, 242, 0.15) 100%);
    color: var(--fluent-primary);
}
.stat-icon.success {
    background: linear-gradient(135deg, rgba(16, 124, 16, 0.15) 0%, rgba(0, 178, 148, 0.15) 100%);
    color: var(--fluent-success);
}
.stat-icon.danger {
    background: linear-gradient(135deg, rgba(209, 52, 56, 0.15) 0%, rgba(255, 67, 67, 0.15) 100%);
    color: var(--fluent-danger);
}
.stat-content {
    display: flex;
    flex-direction: column;
}
.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--fluent-text-primary);
    line-height: 1.2;
}
.stat-label {
    font-size: 13px;
    color: var(--fluent-text-secondary);
}

/* Results Header & Footer */
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

/* Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

/* Data Card */
.data-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    transition: all 0.2s ease;
    overflow: hidden;
    cursor: pointer;
}
.data-card:hover {
    box-shadow: var(--fluent-shadow-16);
    transform: translateY(-2px);
}

.card-header-section {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 20px;
    border-bottom: 1px solid var(--fluent-gray-30);
    background: var(--fluent-gray-20);
}
.card-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--fluent-radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.card-icon.success {
    background: linear-gradient(135deg, #107C10 0%, #0B5A08 100%);
    color: white;
}
.card-icon.danger {
    background: linear-gradient(135deg, #D13438 0%, #a4262c 100%);
    color: white;
}
.card-title-section {
    flex: 1;
    min-width: 0;
}
.card-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    margin: 0 0 6px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.status-badge.active {
    background: rgba(16, 124, 16, 0.12);
    color: #107C10;
}
.status-badge.inactive {
    background: rgba(209, 52, 56, 0.12);
    color: #D13438;
}

.card-body-section {
    padding: 20px;
}

/* Card CTA */
.card-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    margin-top: 16px;
    background: linear-gradient(135deg, rgba(0, 120, 212, 0.08) 0%, rgba(0, 178, 148, 0.08) 100%);
    border-radius: var(--fluent-radius-md);
    border: 1px dashed var(--fluent-primary);
    transition: all 0.2s ease;
}
.data-card:hover .card-cta {
    background: linear-gradient(135deg, rgba(0, 120, 212, 0.15) 0%, rgba(0, 178, 148, 0.15) 100%);
    border-style: solid;
}
.cta-text {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--fluent-primary);
}
.cta-arrow {
    color: var(--fluent-primary);
    font-size: 18px;
    transition: transform 0.2s ease;
}
.data-card:hover .cta-arrow {
    transform: translateX(4px);
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.info-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.info-item.full-width {
    grid-column: 1 / -1;
}
.info-icon {
    width: 36px;
    height: 36px;
    background: var(--fluent-gray-20);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--fluent-primary);
    font-size: 16px;
    flex-shrink: 0;
}
.info-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.info-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fluent-text-tertiary);
}
.info-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    font-family: 'Consolas', monospace;
}

/* Card Actions */
.card-actions {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    gap: 6px;
    z-index: 10;
}
.card-action-icon {
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
.card-action-icon:hover {
    transform: scale(1.1);
}
.card-action-icon.analytics:hover {
    background: linear-gradient(135deg, #00B294 0%, #038387 100%);
    color: white;
}
.card-action-icon.view:hover {
    background: var(--fluent-primary);
    color: white;
}
.card-action-icon.edit:hover {
    background: #FFB900;
    color: white;
}
.card-action-icon.delete:hover {
    background: var(--fluent-danger);
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

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}
.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.modal-container {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-xl);
    box-shadow: var(--fluent-shadow-64);
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow: hidden;
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s ease;
}
.modal-overlay.active .modal-container {
    transform: scale(1) translateY(0);
}
.modal-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px;
    background: var(--fluent-gray-20);
    border-bottom: 1px solid var(--fluent-gray-30);
}
.modal-header-icon {
    width: 52px;
    height: 52px;
    border-radius: var(--fluent-radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}
.modal-header-icon.view {
    background: linear-gradient(135deg, var(--fluent-primary) 0%, #00BCF2 100%);
    color: white;
}
.modal-header-icon.edit {
    background: linear-gradient(135deg, #FFB900 0%, #FF8C00 100%);
    color: white;
}
.modal-header-icon.create {
    background: linear-gradient(135deg, #107C10 0%, #00B294 100%);
    color: white;
}
.modal-header-content {
    flex: 1;
}
.modal-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    margin: 0 0 4px 0;
}
.modal-subtitle {
    font-size: 13px;
    color: var(--fluent-text-secondary);
    margin: 0;
}
.modal-close {
    width: 36px;
    height: 36px;
    border-radius: var(--fluent-radius-md);
    border: none;
    background: transparent;
    color: var(--fluent-text-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.15s ease;
}
.modal-close:hover {
    background: var(--fluent-gray-30);
    color: var(--fluent-text-primary);
}
.modal-body {
    padding: 24px;
    overflow-y: auto;
    max-height: calc(90vh - 180px);
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background: var(--fluent-gray-10);
    border-top: 1px solid var(--fluent-gray-20);
}

/* Modal Loading */
.modal-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    gap: 16px;
    color: var(--fluent-text-secondary);
}
.modal-loading i {
    font-size: 48px;
    color: var(--fluent-primary);
    animation: spin 1s linear infinite;
}

/* View Modal Content */
.view-details {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.view-detail-row {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    background: var(--fluent-gray-10);
    border-radius: var(--fluent-radius-md);
}
.view-detail-icon {
    width: 44px;
    height: 44px;
    background: var(--fluent-primary);
    color: white;
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.view-detail-content {
    flex: 1;
}
.view-detail-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--fluent-text-tertiary);
    margin-bottom: 4px;
}
.view-detail-value {
    font-size: 16px;
    font-weight: 600;
    color: var(--fluent-text-primary);
}
.view-coordinates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Status Toggle */
.status-toggle-group {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}
.status-toggle {
    flex: 1;
    cursor: pointer;
}
.status-toggle input {
    display: none;
}
.status-toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    border-radius: var(--fluent-radius-md);
    border: 2px solid var(--fluent-gray-30);
    background: var(--fluent-bg-primary);
    font-size: 14px;
    font-weight: 500;
    color: var(--fluent-text-secondary);
    transition: all 0.2s ease;
}
.status-toggle input:checked + .status-toggle-btn.active {
    border-color: var(--fluent-success);
    background: rgba(16, 124, 16, 0.08);
    color: var(--fluent-success);
}
.status-toggle input:checked + .status-toggle-btn.inactive {
    border-color: var(--fluent-danger);
    background: rgba(209, 52, 56, 0.08);
    color: var(--fluent-danger);
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
    .stations-grid {
        grid-template-columns: 1fr !important;
        gap: 12px;
    }
    .station-card {
        padding: 16px;
    }
    .station-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .station-card-actions {
        width: 100%;
        justify-content: flex-start;
    }
    .station-stats-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .station-stat {
        padding: 10px;
    }
    .stat-val {
        font-size: 16px;
    }
    .filter-section {
        flex-direction: column;
        gap: 12px;
    }
    .filter-section .fluent-input {
        width: 100%;
    }
    .results-header {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    .pagination-container {
        justify-content: center;
        flex-wrap: wrap;
    }
    /* View Modal */
    .view-modal-container {
        width: 95% !important;
        max-width: 95% !important;
    }
    .view-detail-grid {
        grid-template-columns: 1fr;
    }
    .view-coordinates {
        grid-template-columns: 1fr;
    }
    /* Edit Modal */
    .modal-container {
        width: 95% !important;
        max-width: 95% !important;
    }
    .modal-body {
        padding: 16px;
    }
    .modal-footer {
        flex-direction: column;
        gap: 8px;
    }
    .modal-footer .fluent-btn {
        width: 100%;
        justify-content: center;
    }
    .status-toggle-group {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .fluent-page-title {
        font-size: 18px;
    }
    .station-stats-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('scripts')
<script>
let currentPage = 1;
let currentStationId = null;
let searchTimeout = null;
const perPage = 12;

$(function() {
    // Load data immediately
    loadStations();

    // Search with debounce
    $('#search_input').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadStations(1), 300);
    });

    // Status filter
    $('#status_filter').on('change', function() {
        loadStations(1);
    });
});

function loadStations(page = 1) {
    currentPage = page;
    $('#loading_state').show();
    $('#stations_grid').hide();
    $('#empty_state').hide();
    $('#pagination_top, #pagination_bottom').hide();

    $.ajax({
        url: "{{ route('admin.stations.index') }}",
        type: 'GET',
        data: {
            page: page,
            per_page: perPage,
            search: $('#search_input').val(),
            status: $('#status_filter').val()
        },
        success: function(response) {
            $('#loading_state').hide();

            // Update stats
            if (response.stats) {
                $('#stat_total').text(response.stats.total);
                $('#stat_active').text(response.stats.active);
                $('#stat_inactive').text(response.stats.inactive);
            }

            if (response.data && response.data.length > 0) {
                renderCards(response.data);
                renderPagination(response);
                $('#results_count').html(`Showing <strong>${response.from || 0}-${response.to || 0}</strong> of <strong>${response.total}</strong> stations`);
                $('#stations_grid').show();
                $('#pagination_top, #pagination_bottom').show();
            } else {
                $('#empty_state').show();
                $('#results_count').html('No stations found');
            }
        },
        error: function() {
            $('#loading_state').hide();
            $('#empty_state').show();
            $('#results_count').html('Error loading stations');
        }
    });
}

function renderCards(data) {
    let html = '';
    data.forEach(function(station) {
        const statusClass = station.status == '1' ? 'success' : 'danger';
        const statusText = station.status == '1' ? 'Active' : 'Inactive';
        const statusIcon = station.status == '1' ? 'checkbox-circle' : 'close-circle';

        html += `
            <div class="data-card" style="position: relative;">
                <div class="card-actions">
                    @can('station_show')
                    <button type="button" class="card-action-icon analytics" title="Analytics" onclick="event.stopPropagation(); window.location.href='/admin/daily-weathers?mode=stationAnalytics&station=${station.id}'">
                        <i class="ri-bar-chart-box-line"></i>
                    </button>
                    <button type="button" class="card-action-icon view" title="View" onclick="event.stopPropagation(); openViewModal(${station.id})">
                        <i class="ri-eye-line"></i>
                    </button>
                    @endcan
                    @can('station_edit')
                    <button type="button" class="card-action-icon edit" title="Edit" onclick="event.stopPropagation(); openEditModal(${station.id})">
                        <i class="ri-pencil-line"></i>
                    </button>
                    @endcan
                    @can('station_delete')
                    <button type="button" class="card-action-icon delete" title="Delete" onclick="event.stopPropagation(); deleteStation(${station.id})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    @endcan
                </div>
                <div class="card-header-section">
                    <div class="card-icon ${statusClass}">
                        <i class="ri-map-pin-2-line"></i>
                    </div>
                    <div class="card-title-section">
                        <h3 class="card-title">${escapeHtml(station.station_name)}</h3>
                        <div class="card-meta">
                            <span class="status-badge ${station.status == '1' ? 'active' : 'inactive'}">
                                <i class="ri-${statusIcon}-line"></i>
                                ${statusText}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body-section">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-compass-3-line"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Latitude</span>
                                <span class="info-value">${station.lat ?? 'N/A'}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-compass-line"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Longitude</span>
                                <span class="info-value">${station.lon ?? 'N/A'}</span>
                            </div>
                        </div>
                        <div class="info-item full-width">
                            <div class="info-icon">
                                <i class="ri-arrow-up-line"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Elevation</span>
                                <span class="info-value">${station.elevation ? station.elevation + 'm' : 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    $('#stations_grid').html(html);
}

function renderPagination(response) {
    const { current_page, last_page } = response;
    let html = '<div class="pagination-container">';

    html += `<button class="page-btn" onclick="goToPage(${current_page - 1})" ${current_page === 1 ? 'disabled' : ''}><i class="ri-arrow-left-s-line"></i></button>`;

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

    html += `<button class="page-btn" onclick="goToPage(${current_page + 1})" ${current_page === last_page ? 'disabled' : ''}><i class="ri-arrow-right-s-line"></i></button>`;
    html += '</div>';

    $('#pagination_top, #pagination_bottom').html(html);
}

function goToPage(page) {
    loadStations(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => closeModal(modal.id));
    }
});

// View Modal
function openViewModal(id) {
    currentStationId = id;
    openModal('viewModal');
    document.getElementById('viewModalContent').innerHTML = `
        <div class="modal-loading">
            <i class="ri-loader-4-line"></i>
            <span>Loading...</span>
        </div>
    `;

    fetch(`/admin/stations/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        const station = data.station;
        document.getElementById('viewModalSubtitle').textContent = station.station_name;
        document.getElementById('viewModalContent').innerHTML = `
            <div class="view-details">
                <div class="view-detail-row">
                    <div class="view-detail-icon"><i class="ri-map-pin-line"></i></div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Station Name</div>
                        <div class="view-detail-value">${escapeHtml(station.station_name)}</div>
                    </div>
                </div>
                <div class="view-coordinates">
                    <div class="view-detail-row">
                        <div class="view-detail-icon" style="background: linear-gradient(135deg, #00B294 0%, #038387 100%);"><i class="ri-compass-3-line"></i></div>
                        <div class="view-detail-content">
                            <div class="view-detail-label">Latitude</div>
                            <div class="view-detail-value" style="font-family: 'Consolas', monospace;">${station.lat ?? 'N/A'}</div>
                        </div>
                    </div>
                    <div class="view-detail-row">
                        <div class="view-detail-icon" style="background: linear-gradient(135deg, #8764B8 0%, #5C2D91 100%);"><i class="ri-compass-line"></i></div>
                        <div class="view-detail-content">
                            <div class="view-detail-label">Longitude</div>
                            <div class="view-detail-value" style="font-family: 'Consolas', monospace;">${station.lon ?? 'N/A'}</div>
                        </div>
                    </div>
                </div>
                <div class="view-detail-row">
                    <div class="view-detail-icon" style="background: linear-gradient(135deg, #FFB900 0%, #FF8C00 100%);"><i class="ri-arrow-up-line"></i></div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Elevation</div>
                        <div class="view-detail-value">${station.elevation ? station.elevation + 'm' : 'N/A'}</div>
                    </div>
                </div>
                <div class="view-detail-row">
                    <div class="view-detail-icon" style="background: linear-gradient(135deg, ${station.status == '1' ? '#107C10, #0B5A08' : '#D13438, #a4262c'});"><i class="ri-${station.status == '1' ? 'checkbox-circle' : 'close-circle'}-line"></i></div>
                    <div class="view-detail-content">
                        <div class="view-detail-label">Status</div>
                        <div class="view-detail-value">
                            <span class="status-badge ${station.status == '1' ? 'active' : 'inactive'}">
                                <i class="ri-${station.status == '1' ? 'checkbox-circle' : 'close-circle'}-line"></i>
                                ${station.status == '1' ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    })
    .catch(() => {
        document.getElementById('viewModalContent').innerHTML = `
            <div class="modal-loading">
                <i class="ri-error-warning-line" style="font-size: 48px; color: var(--fluent-danger); animation: none;"></i>
                <span>Failed to load station data</span>
            </div>
        `;
    });
}

document.getElementById('viewToEditBtn')?.addEventListener('click', function() {
    closeModal('viewModal');
    if (currentStationId) openEditModal(currentStationId);
});

function openCreateModal() {
    currentStationId = null;
    document.getElementById('editModalIcon').className = 'modal-header-icon create';
    document.getElementById('editModalIcon').innerHTML = '<i class="ri-add-line"></i>';
    document.getElementById('editModalTitle').textContent = 'Add New Station';
    document.getElementById('editModalSubtitle').textContent = 'Create a new station';
    document.getElementById('submitBtnText').textContent = 'Create Station';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('stationForm').action = '{{ route("admin.stations.store") }}';
    document.getElementById('stationForm').reset();
    document.querySelector('input[name="status"][value="1"]').checked = true;
    openModal('editModal');
}

function openEditModal(id) {
    currentStationId = id;
    document.getElementById('editModalIcon').className = 'modal-header-icon edit';
    document.getElementById('editModalIcon').innerHTML = '<i class="ri-pencil-line"></i>';
    document.getElementById('editModalTitle').textContent = 'Edit Station';
    document.getElementById('editModalSubtitle').textContent = 'Update station information';
    document.getElementById('submitBtnText').textContent = 'Save Changes';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('stationForm').action = `/admin/stations/${id}`;
    openModal('editModal');

    fetch(`/admin/stations/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        const station = data.station;
        document.getElementById('modal_station_name').value = station.station_name || '';
        document.getElementById('modal_lat').value = station.lat || '';
        document.getElementById('modal_lon').value = station.lon || '';
        document.getElementById('modal_elevation').value = station.elevation || '';
        const statusValue = station.status !== null ? station.status.toString() : '1';
        document.querySelector(`input[name="status"][value="${statusValue}"]`).checked = true;
    });
}

function deleteStation(id) {
    if (!confirm('Are you sure you want to delete this station?')) return;

    fetch(`/admin/stations/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(() => loadStations(currentPage))
    .catch(() => alert('Failed to delete station'));
}

document.getElementById('stationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    const originalText = document.getElementById('submitBtnText').textContent;

    submitBtn.disabled = true;
    document.getElementById('submitBtnText').textContent = 'Saving...';

    document.querySelectorAll('.fluent-invalid-feedback').forEach(el => el.textContent = '');
    document.querySelectorAll('.fluent-input.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal('editModal');
            loadStations(currentPage);
        } else if (data.errors) {
            Object.keys(data.errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                const errorDiv = document.getElementById(`error_${field}`);
                if (input) input.classList.add('is-invalid');
                if (errorDiv) errorDiv.textContent = data.errors[field][0];
            });
        }
    })
    .catch(error => console.error('Error:', error))
    .finally(() => {
        submitBtn.disabled = false;
        document.getElementById('submitBtnText').textContent = originalText;
    });
});
</script>
@endsection
