@extends('layouts.admin')
@section('title', 'User Management - ' . trans('panel.site_title'))
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-user-settings-line"></i>
        </span>
        User Management
    </h1>
</div>

<!-- Tab Navigation -->
<div class="um-tabs mb-4">
    <button class="um-tab active" data-tab="users">
        <i class="ri-user-line"></i>
        <span>Users</span>
        <span class="um-tab-badge">{{ count($users) }}</span>
    </button>
    <button class="um-tab" data-tab="roles">
        <i class="ri-shield-user-line"></i>
        <span>Roles</span>
        <span class="um-tab-badge">{{ count($roles) }}</span>
    </button>
    <button class="um-tab" data-tab="permissions">
        <i class="ri-key-2-line"></i>
        <span>Permissions</span>
        <span class="um-tab-badge">{{ count($permissions) }}</span>
    </button>
</div>

<!-- ================================== -->
<!-- TAB 1: USERS -->
<!-- ================================== -->
<div id="users_tab" class="um-tab-content active">
    <!-- Header with Search and Add -->
    <div class="um-section-header">
        <div class="um-search-box">
            <i class="ri-search-line"></i>
            <input type="text" id="user_search" placeholder="Search users by name or email...">
        </div>
        @can('user_create')
            <a href="{{ route('admin.users.create') }}" class="fluent-btn fluent-btn-primary">
                <i class="ri-add-line"></i>
                Add User
            </a>
        @endcan
    </div>

    <!-- Users Grid -->
    <div id="users_empty" class="um-empty-state" style="display: none;">
        <div class="um-empty-icon"><i class="ri-user-unfollow-line"></i></div>
        <h3>No Users Found</h3>
        <p>No users match your search criteria.</p>
    </div>

    <div id="users_grid" class="um-users-grid">
        @foreach($users as $user)
        <div class="um-user-card" data-name="{{ strtolower($user->name ?? '') }}" data-email="{{ strtolower($user->email ?? '') }}">
            <div class="um-user-header">
                <div class="um-user-avatar">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <h4 class="um-user-name">{{ $user->name ?? 'N/A' }}</h4>
                <p class="um-user-email">{{ $user->email ?? 'N/A' }}</p>
                @if($user->email_verified_at)
                    <div class="um-verified-badge" title="Verified"><i class="ri-verified-badge-fill"></i></div>
                @endif
            </div>
            <div class="um-user-body">
                <div class="um-user-roles">
                    @forelse($user->roles as $role)
                        <span class="um-role-tag">{{ $role->title }}</span>
                    @empty
                        <span class="um-role-tag empty">No Role</span>
                    @endforelse
                </div>
                <div class="um-user-meta">
                    <span><i class="ri-calendar-line"></i> {{ $user->created_at ? $user->created_at->format('d M Y') : 'N/A' }}</span>
                </div>
            </div>
            <div class="um-user-actions">
                @can('user_show')
                    <a href="{{ route('admin.users.show', $user->id) }}" class="um-action-btn" title="View"><i class="ri-eye-line"></i></a>
                @endcan
                @can('user_edit')
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="um-action-btn" title="Edit"><i class="ri-pencil-line"></i></a>
                @endcan
                @can('user_delete')
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="um-action-btn danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                    </form>
                @endcan
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- ================================== -->
<!-- TAB 2: ROLES -->
<!-- ================================== -->
<div id="roles_tab" class="um-tab-content">
    <!-- Header with Search and Add -->
    <div class="um-section-header">
        <div class="um-search-box">
            <i class="ri-search-line"></i>
            <input type="text" id="role_search" placeholder="Search roles...">
        </div>
        @can('role_create')
            <a href="{{ route('admin.roles.create') }}" class="fluent-btn fluent-btn-primary">
                <i class="ri-add-line"></i>
                Add Role
            </a>
        @endcan
    </div>

    <!-- Roles Grid -->
    <div id="roles_empty" class="um-empty-state" style="display: none;">
        <div class="um-empty-icon"><i class="ri-shield-user-line"></i></div>
        <h3>No Roles Found</h3>
        <p>No roles match your search criteria.</p>
    </div>

    <div id="roles_grid" class="um-roles-grid">
        @foreach($roles as $role)
        <div class="um-role-card" data-title="{{ strtolower($role->title ?? '') }}">
            <div class="um-role-header">
                <div class="um-role-icon">
                    <i class="ri-shield-user-fill"></i>
                </div>
                <div class="um-role-info">
                    <h4>{{ $role->title ?? 'N/A' }}</h4>
                    <span class="um-permission-count">{{ $role->permissions->count() }} permissions</span>
                </div>
            </div>
            <div class="um-role-body">
                <div class="um-permissions-preview">
                    @forelse($role->permissions->take(5) as $permission)
                        <span class="um-permission-tag">{{ $permission->title }}</span>
                    @empty
                        <span class="um-permission-tag empty">No permissions</span>
                    @endforelse
                    @if($role->permissions->count() > 5)
                        <span class="um-permission-tag more">+{{ $role->permissions->count() - 5 }}</span>
                    @endif
                </div>
            </div>
            <div class="um-role-actions">
                @can('role_show')
                    <a href="{{ route('admin.roles.show', $role->id) }}" class="um-action-btn" title="View"><i class="ri-eye-line"></i></a>
                @endcan
                @can('role_edit')
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="um-action-btn" title="Edit"><i class="ri-pencil-line"></i></a>
                @endcan
                @can('role_delete')
                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="um-action-btn danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                    </form>
                @endcan
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- ================================== -->
<!-- TAB 3: PERMISSIONS -->
<!-- ================================== -->
<div id="permissions_tab" class="um-tab-content">
    <!-- Header with Search and Add -->
    <div class="um-section-header">
        <div class="um-search-box">
            <i class="ri-search-line"></i>
            <input type="text" id="permission_search" placeholder="Search permissions...">
        </div>
        @can('permission_create')
            <a href="{{ route('admin.permissions.create') }}" class="fluent-btn fluent-btn-primary">
                <i class="ri-add-line"></i>
                Add Permission
            </a>
        @endcan
    </div>

    <!-- Permissions Grid -->
    <div id="permissions_empty" class="um-empty-state" style="display: none;">
        <div class="um-empty-icon"><i class="ri-key-2-line"></i></div>
        <h3>No Permissions Found</h3>
        <p>No permissions match your search criteria.</p>
    </div>

    @php
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $parts = explode('_', $permission->title);
            array_pop($parts);
            return implode('_', $parts) ?: 'other';
        });
    @endphp

    <div id="permissions_container" class="um-permissions-container">
        @foreach($groupedPermissions as $group => $perms)
        <div class="um-permission-group" data-group="{{ strtolower($group) }}">
            <div class="um-permission-group-header">
                <div class="um-group-icon">
                    @php
                        $icon = 'ri-key-2-line';
                        if (str_contains($group, 'user')) $icon = 'ri-user-line';
                        elseif (str_contains($group, 'role')) $icon = 'ri-shield-user-line';
                        elseif (str_contains($group, 'permission')) $icon = 'ri-key-2-line';
                        elseif (str_contains($group, 'station')) $icon = 'ri-map-pin-line';
                        elseif (str_contains($group, 'weather')) $icon = 'ri-sun-cloudy-line';
                        elseif (str_contains($group, 'tender')) $icon = 'ri-file-list-3-line';
                        elseif (str_contains($group, 'item')) $icon = 'ri-archive-line';
                        elseif (str_contains($group, 'master')) $icon = 'ri-database-2-line';
                        elseif (str_contains($group, 'profile')) $icon = 'ri-user-settings-line';
                    @endphp
                    <i class="{{ $icon }}"></i>
                </div>
                <div class="um-group-info">
                    <h4>{{ ucwords(str_replace('_', ' ', $group)) }}</h4>
                    <span>{{ $perms->count() }} {{ $perms->count() == 1 ? 'permission' : 'permissions' }}</span>
                </div>
                <button class="um-group-toggle" onclick="togglePermissionGroup(this)">
                    <i class="ri-arrow-down-s-line"></i>
                </button>
            </div>
            <div class="um-permission-group-body">
                @foreach($perms as $permission)
                <div class="um-permission-item" data-title="{{ strtolower($permission->title ?? '') }}">
                    <div class="um-permission-main">
                        <span class="um-permission-code">{{ $permission->title ?? 'N/A' }}</span>
                    </div>
                    <div class="um-permission-actions">
                        @can('permission_show')
                            <a href="{{ route('admin.permissions.show', $permission->id) }}" class="um-action-btn sm" title="View"><i class="ri-eye-line"></i></a>
                        @endcan
                        @can('permission_edit')
                            <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="um-action-btn sm" title="Edit"><i class="ri-pencil-line"></i></a>
                        @endcan
                        @can('permission_delete')
                            <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display: inline;">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="um-action-btn sm danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
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
@endsection

@section('styles')
<style>
/* Tab Navigation */
.um-tabs {
    display: flex;
    gap: 8px;
    background: var(--fluent-bg-primary);
    padding: 8px;
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
}
.um-tab {
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
.um-tab:hover {
    background: var(--fluent-gray-20);
    color: var(--fluent-text-primary);
}
.um-tab.active {
    background: linear-gradient(135deg, #6B4FBB 0%, #8764B8 100%);
    color: white;
    box-shadow: var(--fluent-shadow-8);
}
.um-tab i {
    font-size: 18px;
}
.um-tab-badge {
    background: rgba(255,255,255,0.2);
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.um-tab:not(.active) .um-tab-badge {
    background: var(--fluent-gray-30);
    color: var(--fluent-text-secondary);
}

/* Tab Content */
.um-tab-content {
    display: none;
}
.um-tab-content.active {
    display: block;
}

/* Section Header */
.um-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.um-search-box {
    position: relative;
    flex: 1;
    max-width: 400px;
}
.um-search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--fluent-text-tertiary);
    font-size: 18px;
}
.um-search-box input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: 1px solid var(--fluent-gray-30);
    border-radius: var(--fluent-radius-md);
    background: var(--fluent-bg-primary);
    font-size: 14px;
    color: var(--fluent-text-primary);
    transition: all 0.15s ease;
}
.um-search-box input:focus {
    outline: none;
    border-color: var(--fluent-primary);
    box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.1);
}

/* Empty State */
.um-empty-state {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    padding: 80px 20px;
    text-align: center;
}
.um-empty-icon {
    width: 80px;
    height: 80px;
    background: var(--fluent-gray-20);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: var(--fluent-text-tertiary);
}
.um-empty-state h3 {
    color: var(--fluent-text-primary);
    margin: 0 0 8px;
}
.um-empty-state p {
    color: var(--fluent-text-secondary);
    margin: 0;
}

/* ================================== */
/* USERS GRID */
/* ================================== */
.um-users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.um-user-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
    transition: all 0.2s ease;
}
.um-user-card:hover {
    box-shadow: var(--fluent-shadow-16);
    transform: translateY(-2px);
}
.um-user-header {
    background: linear-gradient(135deg, #6B4FBB 0%, #8764B8 100%);
    padding: 24px;
    text-align: center;
    position: relative;
}
.um-user-avatar {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.2);
    border: 3px solid rgba(255,255,255,0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 24px;
    font-weight: 600;
    color: white;
}
.um-verified-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    color: #50E6FF;
    font-size: 20px;
}
.um-user-body {
    padding: 20px;
    text-align: center;
}
.um-user-name {
    font-size: 16px;
    font-weight: 600;
    color: white;
    margin: 8px 0 4px;
}
.um-user-email {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.85);
    margin: 0;
}
.um-user-roles {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 12px;
}
.um-role-tag {
    background: linear-gradient(135deg, rgba(107, 79, 187, 0.1) 0%, rgba(135, 100, 184, 0.1) 100%);
    color: #6B4FBB;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.um-role-tag.empty {
    background: var(--fluent-gray-20);
    color: var(--fluent-text-tertiary);
}
.um-user-meta {
    font-size: 12px;
    color: var(--fluent-text-tertiary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.um-user-actions {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    background: var(--fluent-gray-10);
    border-top: 1px solid var(--fluent-gray-20);
}

/* ================================== */
/* ROLES GRID */
/* ================================== */
.um-roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.um-role-card {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
    transition: all 0.2s ease;
}
.um-role-card:hover {
    box-shadow: var(--fluent-shadow-16);
    transform: translateY(-2px);
}
.um-role-header {
    background: linear-gradient(135deg, #D83B01 0%, #FF8C00 100%);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.um-role-icon {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}
.um-role-info h4 {
    font-size: 18px;
    font-weight: 600;
    color: white;
    margin: 0 0 4px;
}
.um-permission-count {
    font-size: 13px;
    color: rgba(255,255,255,0.8);
}
.um-role-body {
    padding: 20px;
}
.um-permissions-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.um-permission-tag {
    background: var(--fluent-gray-10);
    color: var(--fluent-text-secondary);
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-family: 'Consolas', monospace;
}
.um-permission-tag.empty {
    background: var(--fluent-gray-20);
    color: var(--fluent-text-tertiary);
    font-style: italic;
    font-family: inherit;
}
.um-permission-tag.more {
    background: #D83B01;
    color: white;
    font-family: inherit;
}
.um-role-actions {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    background: var(--fluent-gray-10);
    border-top: 1px solid var(--fluent-gray-20);
}

/* ================================== */
/* PERMISSIONS */
/* ================================== */
.um-permissions-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.um-permission-group {
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-lg);
    box-shadow: var(--fluent-shadow-4);
    border: 1px solid var(--fluent-gray-20);
    overflow: hidden;
}
.um-permission-group-header {
    background: linear-gradient(135deg, #107C10 0%, #54B054 100%);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
}
.um-group-icon {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: var(--fluent-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}
.um-group-info {
    flex: 1;
}
.um-group-info h4 {
    font-size: 15px;
    font-weight: 600;
    color: white;
    margin: 0 0 2px;
}
.um-group-info span {
    font-size: 12px;
    color: rgba(255,255,255,0.8);
}
.um-group-toggle {
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.2);
    border: none;
    border-radius: var(--fluent-radius-sm);
    color: white;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}
.um-permission-group.collapsed .um-group-toggle {
    transform: rotate(-90deg);
}
.um-permission-group-body {
    padding: 8px 12px;
    max-height: 400px;
    overflow-y: auto;
}
.um-permission-group.collapsed .um-permission-group-body {
    display: none;
}
.um-permission-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-radius: var(--fluent-radius-md);
    transition: background 0.15s ease;
}
.um-permission-item:hover {
    background: var(--fluent-gray-10);
}
.um-permission-main {
    flex: 1;
}
.um-permission-code {
    background: linear-gradient(135deg, rgba(16, 124, 16, 0.1) 0%, rgba(84, 176, 84, 0.1) 100%);
    color: #107C10;
    padding: 6px 12px;
    border-radius: var(--fluent-radius-sm);
    font-size: 12px;
    font-weight: 500;
    font-family: 'Consolas', monospace;
}
.um-permission-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.um-permission-item:hover .um-permission-actions {
    opacity: 1;
}

/* Action Buttons */
.um-action-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--fluent-gray-30);
    background: var(--fluent-bg-primary);
    border-radius: var(--fluent-radius-md);
    color: var(--fluent-text-secondary);
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 16px;
}
.um-action-btn:hover {
    background: var(--fluent-primary);
    border-color: var(--fluent-primary);
    color: white;
}
.um-action-btn.danger:hover {
    background: #D13438;
    border-color: #D13438;
}
.um-action-btn.sm {
    width: 28px;
    height: 28px;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .um-tabs {
        flex-direction: column;
    }
    .um-section-header {
        flex-direction: column;
        align-items: stretch;
    }
    .um-search-box {
        max-width: none;
    }
    .um-users-grid, .um-roles-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('scripts')
@parent
<script>
$(function() {
    // Tab switching
    $('.um-tab').on('click', function() {
        const tab = $(this).data('tab');

        $('.um-tab').removeClass('active');
        $(this).addClass('active');

        $('.um-tab-content').removeClass('active');
        $('#' + tab + '_tab').addClass('active');
    });

    // User search
    $('#user_search').on('input', function() {
        const search = $(this).val().toLowerCase().trim();
        let visibleCount = 0;

        $('.um-user-card').each(function() {
            const name = $(this).data('name');
            const email = $(this).data('email');

            if (name.includes(search) || email.includes(search)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        if (visibleCount === 0) {
            $('#users_empty').show();
            $('#users_grid').hide();
        } else {
            $('#users_empty').hide();
            $('#users_grid').show();
        }
    });

    // Role search
    $('#role_search').on('input', function() {
        const search = $(this).val().toLowerCase().trim();
        let visibleCount = 0;

        $('.um-role-card').each(function() {
            const title = $(this).data('title');

            if (title.includes(search)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        if (visibleCount === 0) {
            $('#roles_empty').show();
            $('#roles_grid').hide();
        } else {
            $('#roles_empty').hide();
            $('#roles_grid').show();
        }
    });

    // Permission search
    $('#permission_search').on('input', function() {
        const search = $(this).val().toLowerCase().trim();
        let visibleCount = 0;

        if (search === '') {
            $('.um-permission-group, .um-permission-item').show();
            $('#permissions_empty').hide();
            $('#permissions_container').show();
            return;
        }

        $('.um-permission-group').each(function() {
            const group = $(this);
            const groupName = group.data('group');
            let groupHasVisible = false;

            group.find('.um-permission-item').each(function() {
                const item = $(this);
                const title = item.data('title');

                if (title.includes(search) || groupName.includes(search)) {
                    item.show();
                    groupHasVisible = true;
                    visibleCount++;
                } else {
                    item.hide();
                }
            });

            if (groupHasVisible) {
                group.show();
                group.removeClass('collapsed');
            } else {
                group.hide();
            }
        });

        if (visibleCount === 0) {
            $('#permissions_empty').show();
            $('#permissions_container').hide();
        } else {
            $('#permissions_empty').hide();
            $('#permissions_container').show();
        }
    });
});

function togglePermissionGroup(btn) {
    $(btn).closest('.um-permission-group').toggleClass('collapsed');
}
</script>
@endsection
