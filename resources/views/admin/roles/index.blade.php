@extends('layouts.admin')
@section('title', 'Roles - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-admin-line"></i>
        </span>
        {{ trans('cruds.role.title_singular') }} {{ trans('global.list') }}
    </h1>
    @can('role_create')
        <div class="fluent-page-actions">
            <a class="fluent-btn fluent-btn-primary" href="{{ route('admin.roles.create') }}">
                <i class="ri-add-line"></i>
                {{ trans('global.add') }} {{ trans('cruds.role.title_singular') }}
            </a>
        </div>
    @endcan
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-shield-user-line fluent-card-title-icon"></i>
            All Roles
        </h3>
        <div class="fluent-card-actions">
            <span class="fluent-badge fluent-badge-primary">{{ count($roles) }} Roles</span>
        </div>
    </div>
    <div class="fluent-card-body">
        <div class="roles-cards-grid">
            @foreach($roles as $role)
            <div class="role-card">
                <div class="role-card-header">
                    <div class="role-icon">
                        <i class="ri-shield-user-fill"></i>
                    </div>
                    <h4 class="role-title">{{ $role->title ?? 'N/A' }}</h4>
                </div>
                <div class="role-card-body">
                    <div class="role-stats">
                        <div class="role-stat">
                            <span class="role-stat-value">{{ $role->permissions->count() }}</span>
                            <span class="role-stat-label">Permissions</span>
                        </div>
                    </div>
                    <div class="role-permissions">
                        <div class="role-permissions-label">
                            <i class="ri-key-2-line mr-1"></i>Assigned Permissions
                        </div>
                        <div class="role-permissions-list">
                            @forelse($role->permissions->take(6) as $permission)
                                <span class="fluent-badge fluent-badge-info">{{ $permission->title }}</span>
                            @empty
                                <span class="text-muted">No permissions assigned</span>
                            @endforelse
                            @if($role->permissions->count() > 6)
                                <span class="fluent-badge fluent-badge-secondary">+{{ $role->permissions->count() - 6 }} more</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="role-card-footer">
                    @can('role_show')
                        <a href="{{ route('admin.roles.show', $role->id) }}" class="fluent-btn fluent-btn-ghost fluent-btn-sm" title="View">
                            <i class="ri-eye-line"></i>
                        </a>
                    @endcan
                    @can('role_edit')
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="fluent-btn fluent-btn-ghost fluent-btn-sm" title="Edit">
                            <i class="ri-pencil-line"></i>
                        </a>
                    @endcan
                    @can('role_delete')
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
</div>
@endsection

@section('styles')
<style>
.roles-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.role-card {
    background: var(--fluent-card-bg);
    border: 1px solid var(--fluent-gray-20);
    border-radius: var(--fluent-radius-lg);
    overflow: hidden;
    transition: all 0.2s ease;
}
.role-card:hover {
    box-shadow: var(--fluent-shadow-md);
    transform: translateY(-2px);
}
.role-card-header {
    background: var(--fluent-gray-20);
    border-bottom: 1px solid var(--fluent-gray-30);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.role-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #D83B01 0%, #FF8C00 100%);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}
.role-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--fluent-text-primary);
    margin: 0;
}
.role-card-body {
    padding: 20px;
}
.role-stats {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}
.role-stat {
    text-align: center;
    padding: 12px 24px;
    background: var(--fluent-gray-10);
    border-radius: var(--fluent-radius-md);
}
.role-stat-value {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: #D83B01;
}
.role-stat-label {
    font-size: 12px;
    color: var(--fluent-text-tertiary);
    text-transform: uppercase;
}
.role-permissions {
    margin-top: 16px;
}
.role-permissions-label {
    font-size: 12px;
    color: var(--fluent-text-tertiary);
    text-transform: uppercase;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}
.role-permissions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    min-height: 60px;
}
.role-card-footer {
    padding: 12px 16px;
    background: var(--fluent-gray-10);
    border-top: 1px solid var(--fluent-gray-20);
    display: flex;
    justify-content: center;
    gap: 8px;
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
    .roles-grid {
        grid-template-columns: 1fr !important;
        gap: 12px;
    }
    .role-card {
        padding: 0;
    }
    .role-card-header {
        padding: 16px;
    }
    .role-card-body {
        padding: 16px;
    }
    .role-card-footer {
        flex-direction: column;
        gap: 8px;
    }
    .role-card-footer .fluent-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .fluent-page-title {
        font-size: 18px;
    }
}
</style>
@endsection
