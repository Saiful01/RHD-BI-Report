@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-user-line"></i>
        </span>
        {{ trans('global.show') }} {{ trans('cruds.user.title') }}
    </h1>
    <div class="fluent-page-actions">
        <a href="{{ route('admin.users.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line"></i>
            {{ trans('global.back_to_list') }}
        </a>
        @can('user_edit')
            <a href="{{ route('admin.users.edit', $user->id) }}" class="fluent-btn fluent-btn-primary">
                <i class="ri-pencil-line"></i>
                Edit User
            </a>
        @endcan
    </div>
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-information-line fluent-card-title-icon"></i>
            User Details
        </h3>
    </div>

    <div class="fluent-card-body">
        <div class="d-flex align-center gap-4 mb-5 pb-4 border-bottom">
            <div class="fluent-user-avatar" style="width: 80px; height: 80px; font-size: 32px;">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <h2 class="mb-1">{{ $user->name }}</h2>
                <p class="text-secondary mb-2">{{ $user->email }}</p>
                @foreach($user->roles as $key => $roles)
                    <span class="fluent-badge fluent-badge-primary">{{ $roles->title }}</span>
                @endforeach
            </div>
        </div>

        <div class="fluent-table-wrapper">
            <table class="fluent-table">
                <tbody>
                    <tr>
                        <th style="width: 200px;">
                            <div class="d-flex align-center gap-2">
                                <i class="ri-hashtag text-primary"></i>
                                {{ trans('cruds.user.fields.id') }}
                            </div>
                        </th>
                        <td>
                            <span class="fluent-badge fluent-badge-primary">#{{ $user->id }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-user-line text-primary"></i>
                                {{ trans('cruds.user.fields.name') }}
                            </div>
                        </th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-mail-line text-primary"></i>
                                {{ trans('cruds.user.fields.email') }}
                            </div>
                        </th>
                        <td>
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-checkbox-circle-line text-primary"></i>
                                {{ trans('cruds.user.fields.email_verified_at') }}
                            </div>
                        </th>
                        <td>
                            @if($user->email_verified_at)
                                <span class="fluent-badge fluent-badge-success">
                                    <i class="ri-check-line mr-1"></i>
                                    Verified on {{ $user->email_verified_at->format('M d, Y') }}
                                </span>
                            @else
                                <span class="fluent-badge fluent-badge-warning">Not Verified</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="d-flex align-center gap-2">
                                <i class="ri-shield-user-line text-primary"></i>
                                {{ trans('cruds.user.fields.roles') }}
                            </div>
                        </th>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($user->roles as $key => $roles)
                                    <span class="fluent-badge fluent-badge-primary">
                                        <i class="ri-admin-line mr-1"></i>{{ $roles->title }}
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
        <a href="{{ route('admin.users.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line mr-1"></i>
            {{ trans('global.back_to_list') }}
        </a>
    </div>
</div>
@endsection
