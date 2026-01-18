@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-lock-2-line"></i>
        </span>
        {{ trans('global.show') }} {{ trans('cruds.permission.title') }}
    </h1>
    <div class="fluent-page-actions">
        <a href="{{ route('admin.permissions.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line"></i>
            {{ trans('global.back_to_list') }}
        </a>
        @can('permission_edit')
            <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="fluent-btn fluent-btn-primary">
                <i class="ri-pencil-line"></i>
                Edit Permission
            </a>
        @endcan
    </div>
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-information-line fluent-card-title-icon"></i>
            Permission Details
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
                                {{ trans('cruds.permission.fields.id') }}
                            </div>
                        </th>
                        <td>
                            <span class="fluent-badge fluent-badge-primary">#{{ $permission->id }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-key-line text-primary"></i>
                                {{ trans('cruds.permission.fields.title') }}
                            </div>
                        </th>
                        <td>
                            <code class="fluent-badge fluent-badge-info">{{ $permission->title }}</code>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-text text-primary"></i>
                                {{ trans('cruds.permission.fields.display_name') }}
                            </div>
                        </th>
                        <td>{{ $permission->display_name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="fluent-card-footer">
        <a href="{{ route('admin.permissions.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line mr-1"></i>
            {{ trans('global.back_to_list') }}
        </a>
    </div>
</div>
@endsection
