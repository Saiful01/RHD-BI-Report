@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-shield-user-line"></i>
        </span>
        {{ trans('global.show') }} {{ trans('cruds.role.title') }}
    </h1>
    <div class="fluent-page-actions">
        <a href="{{ route('admin.roles.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line"></i>
            {{ trans('global.back_to_list') }}
        </a>
        @can('role_edit')
            <a href="{{ route('admin.roles.edit', $role->id) }}" class="fluent-btn fluent-btn-primary">
                <i class="ri-pencil-line"></i>
                Edit Role
            </a>
        @endcan
    </div>
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-information-line fluent-card-title-icon"></i>
            Role Details
        </h3>
    </div>

    <div class="fluent-card-body">
        <div class="fluent-table-wrapper">
            <table class="fluent-table">
                <tbody>
                    <tr>
                        <th style="width: 200px;">
                            <div class="d-flex align-center gap-2">
                                <i class="ri-hashtag text-primary"></i>
                                {{ trans('cruds.role.fields.id') }}
                            </div>
                        </th>
                        <td>
                            <span class="fluent-badge fluent-badge-primary">#{{ $role->id }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-shield-line text-primary"></i>
                                {{ trans('cruds.role.fields.title') }}
                            </div>
                        </th>
                        <td>
                            <span class="fluent-badge fluent-badge-success">{{ $role->title }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-lock-2-line text-primary"></i>
                                {{ trans('cruds.role.fields.permissions') }}
                            </div>
                        </th>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($role->permissions as $key => $permissions)
                                    <span class="fluent-badge fluent-badge-info">
                                        <i class="ri-key-line mr-1"></i>{{ $permissions->title }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="fluent-card-footer">
        <a href="{{ route('admin.roles.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line mr-1"></i>
            {{ trans('global.back_to_list') }}
        </a>
    </div>
</div>
@endsection
