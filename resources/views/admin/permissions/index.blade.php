@extends('layouts.admin')
@section('title', 'Permissions - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-shield-keyhole-line"></i>
        </span>
        {{ trans('cruds.permission.title_singular') }} {{ trans('global.list') }}
    </h1>
    @can('permission_create')
        <div class="fluent-page-actions">
            <a class="fluent-btn fluent-btn-primary" href="{{ route('admin.permissions.create') }}">
                <i class="ri-add-line"></i>
                {{ trans('global.add') }} {{ trans('cruds.permission.title_singular') }}
            </a>
        </div>
    @endcan
</div>

<div class="fluent-card mb-4">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-search-line fluent-card-title-icon"></i>
            Search Permissions
        </h3>
    </div>
    <div class="fluent-card-body">
        <div class="form-row">
            <div class="form-col" style="max-width: 400px;">
                <div class="fluent-form-group mb-0">
                    <div class="fluent-input-icon">
                        <i class="ri-search-line"></i>
                        <input type="text" id="permission_search" class="fluent-input" placeholder="Search permissions...">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-key-2-line fluent-card-title-icon"></i>
            All Permissions
        </h3>
        <div class="fluent-card-actions">
            <span class="fluent-badge fluent-badge-primary">{{ count($permissions) }} Permissions</span>
        </div>
    </div>
    <div class="fluent-card-body">
        <div id="empty_state" class="empty-state" style="display: none;">
            <div class="empty-state-icon">
                <i class="ri-key-2-line"></i>
            </div>
            <h4>No Permissions Found</h4>
            <p>No permissions match your search criteria.</p>
        </div>

        @php
            $groupedPermissions = $permissions->groupBy(function($permission) {
                $parts = explode('_', $permission->title);
                array_pop($parts);
                return implode('_', $parts) ?: 'other';
            });
        @endphp

        <div id="permissions_container" class="permissions-grouped">
            @foreach($groupedPermissions as $group => $perms)
            <div class="permission-group" data-group="{{ strtolower($group) }}">
                <div class="permission-group-header">
                    <div class="permission-group-icon">
                        <i class="ri-folder-shield-line"></i>
                    </div>
                    <div class="permission-group-info">
                        <h4 class="permission-group-title">{{ ucwords(str_replace('_', ' ', $group)) }}</h4>
                        <span class="permission-group-count">{{ $perms->count() }} permissions</span>
                    </div>
                </div>
                <div class="permission-group-body">
                    @foreach($perms as $permission)
                    <div class="permission-item" data-title="{{ strtolower($permission->title ?? '') }}" data-display="{{ strtolower($permission->display_name ?? '') }}">
                        <div class="permission-item-main">
                            <span class="permission-badge">
                                <i class="ri-key-line mr-1"></i>{{ $permission->title ?? 'N/A' }}
                            </span>
                            @if($permission->display_name)
                                <span class="permission-display">{{ $permission->display_name }}</span>
                            @endif
                        </div>
                        <div class="permission-item-actions">
                            @can('permission_show')
                                <a href="{{ route('admin.permissions.show', $permission->id) }}" class="fluent-btn fluent-btn-ghost fluent-btn-sm" title="View">
                                    <i class="ri-eye-line"></i>
                                </a>
                            @endcan
                            @can('permission_edit')
                                <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="fluent-btn fluent-btn-ghost fluent-btn-sm" title="Edit">
                                    <i class="ri-pencil-line"></i>
                                </a>
                            @endcan
                            @can('permission_delete')
                                <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="fluent-btn fluent-btn-ghost fluent-btn-sm text-danger" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.fluent-input-icon {
    position: relative;
}
.fluent-input-icon i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--fluent-text-tertiary);
}
.fluent-input-icon .fluent-input {
    padding-left: 38px;
}
.permissions-grouped {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.permission-group {
    background: var(--fluent-card-bg);
    border: 1px solid var(--fluent-gray-20);
    border-radius: var(--fluent-radius-lg);
    overflow: hidden;
}
.permission-group-header {
    background: var(--fluent-gray-20);
    border-bottom: 1px solid var(--fluent-gray-30);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.permission-group-icon {
    width: 40px;
    height: 40px;
    background: var(--fluent-primary);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}
.permission-group-info {
    flex: 1;
}
.permission-group-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    margin: 0 0 2px 0;
}
.permission-group-count {
    font-size: 12px;
    color: var(--fluent-text-secondary);
}
.permission-group-body {
    padding: 12px;
}
.permission-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-radius: var(--fluent-radius-md);
    transition: background 0.15s ease;
}
.permission-item:hover {
    background: var(--fluent-gray-10);
}
.permission-item-main {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}
.permission-badge {
    background: var(--fluent-primary-light);
    color: var(--fluent-primary);
    padding: 4px 10px;
    border-radius: var(--fluent-radius-sm);
    font-size: 12px;
    font-weight: 500;
    font-family: 'Roboto Mono', monospace;
}
.permission-display {
    font-size: 13px;
    color: var(--fluent-text-secondary);
}
.permission-item-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.permission-item:hover .permission-item-actions {
    opacity: 1;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    background: var(--fluent-gray-10);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: var(--fluent-text-tertiary);
}
.empty-state h4 {
    color: var(--fluent-text-primary);
    margin-bottom: 8px;
}
.empty-state p {
    color: var(--fluent-text-secondary);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .fluent-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .fluent-page-actions {
        width: 100%;
        flex-wrap: wrap;
    }
    .fluent-page-actions .fluent-btn {
        flex: 1;
        justify-content: center;
    }
    .permissions-grid {
        grid-template-columns: 1fr !important;
        gap: 12px;
    }
    .permission-card {
        padding: 16px;
    }
    .dataTables_wrapper .d-flex {
        flex-direction: column !important;
        gap: 12px !important;
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
    $('#permission_search').on('input', function() {
        const search = $(this).val().toLowerCase().trim();
        let visibleCount = 0;

        if (search === '') {
            $('.permission-group, .permission-item').show();
            $('#empty_state').hide();
            $('#permissions_container').show();
            return;
        }

        $('.permission-group').each(function() {
            const group = $(this);
            const groupName = group.data('group');
            let groupHasVisible = false;

            group.find('.permission-item').each(function() {
                const item = $(this);
                const title = item.data('title');
                const display = item.data('display');

                if (title.includes(search) || display.includes(search) || groupName.includes(search)) {
                    item.show();
                    groupHasVisible = true;
                    visibleCount++;
                } else {
                    item.hide();
                }
            });

            if (groupHasVisible) {
                group.show();
            } else {
                group.hide();
            }
        });

        if (visibleCount === 0) {
            $('#empty_state').show();
            $('#permissions_container').hide();
        } else {
            $('#empty_state').hide();
            $('#permissions_container').show();
        }
    });
});
</script>
@endsection
