@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-shield-user-line"></i>
        </span>
        {{ trans('global.create') }} {{ trans('cruds.role.title_singular') }}
    </h1>
    <div class="fluent-page-actions">
        <a href="{{ route('admin.roles.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line"></i>
            {{ trans('global.back_to_list') }}
        </a>
    </div>
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-information-line fluent-card-title-icon"></i>
            Role Information
        </h3>
    </div>

    <div class="fluent-card-body">
        <form method="POST" action="{{ route("admin.roles.store") }}" enctype="multipart/form-data">
            @csrf

            <div class="fluent-form-group">
                <label class="fluent-label fluent-label-required" for="title">
                    <i class="ri-shield-line mr-1"></i>{{ trans('cruds.role.fields.title') }}
                </label>
                <input class="fluent-input {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', '') }}" required placeholder="e.g., Admin, Editor, Viewer">
                @if($errors->has('title'))
                    <div class="fluent-invalid-feedback">
                        {{ $errors->first('title') }}
                    </div>
                @endif
                @if(trans('cruds.role.fields.title_helper'))
                    <small class="text-secondary">{{ trans('cruds.role.fields.title_helper') }}</small>
                @endif
            </div>

            <div class="fluent-form-group">
                <label class="fluent-label fluent-label-required" for="permissions">
                    <i class="ri-lock-2-line mr-1"></i>{{ trans('cruds.role.fields.permissions') }}
                </label>
                <div class="mb-2">
                    <span class="fluent-btn fluent-btn-sm fluent-btn-ghost select-all">
                        <i class="ri-checkbox-multiple-line"></i> {{ trans('global.select_all') }}
                    </span>
                    <span class="fluent-btn fluent-btn-sm fluent-btn-ghost deselect-all">
                        <i class="ri-checkbox-blank-line"></i> {{ trans('global.deselect_all') }}
                    </span>
                </div>
                <select class="fluent-select select2 {{ $errors->has('permissions') ? 'is-invalid' : '' }}" name="permissions[]" id="permissions" multiple required>
                    @foreach($permissions as $id => $permission)
                        <option value="{{ $id }}" {{ in_array($id, old('permissions', [])) ? 'selected' : '' }}>{{ $permission }}</option>
                    @endforeach
                </select>
                @if($errors->has('permissions'))
                    <div class="fluent-invalid-feedback">
                        {{ $errors->first('permissions') }}
                    </div>
                @endif
                @if(trans('cruds.role.fields.permissions_helper'))
                    <small class="text-secondary">{{ trans('cruds.role.fields.permissions_helper') }}</small>
                @endif
            </div>

            <div class="fluent-card-footer mt-4 p-0 border-0 bg-transparent justify-start">
                <button class="fluent-btn fluent-btn-primary" type="submit">
                    <i class="ri-save-line mr-1"></i>
                    {{ trans('global.save') }}
                </button>
                <a href="{{ route('admin.roles.index') }}" class="fluent-btn fluent-btn-secondary">
                    <i class="ri-close-line mr-1"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
