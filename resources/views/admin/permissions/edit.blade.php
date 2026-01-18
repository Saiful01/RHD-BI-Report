@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-lock-2-line"></i>
        </span>
        {{ trans('global.edit') }} {{ trans('cruds.permission.title_singular') }}
    </h1>
    <div class="fluent-page-actions">
        <a href="{{ route('admin.permissions.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line"></i>
            {{ trans('global.back_to_list') }}
        </a>
    </div>
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-information-line fluent-card-title-icon"></i>
            Edit Permission
        </h3>
    </div>

    <div class="fluent-card-body">
        <form method="POST" action="{{ route("admin.permissions.update", [$permission->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf

            <div class="form-row">
                <div class="form-col">
                    <div class="fluent-form-group">
                        <label class="fluent-label fluent-label-required" for="title">
                            <i class="ri-key-line mr-1"></i>{{ trans('cruds.permission.fields.title') }}
                        </label>
                        <input class="fluent-input {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $permission->title) }}" required placeholder="e.g., user_access">
                        @if($errors->has('title'))
                            <div class="fluent-invalid-feedback">
                                {{ $errors->first('title') }}
                            </div>
                        @endif
                        @if(trans('cruds.permission.fields.title_helper'))
                            <small class="text-secondary">{{ trans('cruds.permission.fields.title_helper') }}</small>
                        @endif
                    </div>
                </div>

                <div class="form-col">
                    <div class="fluent-form-group">
                        <label class="fluent-label fluent-label-required" for="display_name">
                            <i class="ri-text mr-1"></i>{{ trans('cruds.permission.fields.display_name') }}
                        </label>
                        <input class="fluent-input {{ $errors->has('display_name') ? 'is-invalid' : '' }}" type="text" name="display_name" id="display_name" value="{{ old('display_name', $permission->display_name) }}" required placeholder="e.g., Access Users">
                        @if($errors->has('display_name'))
                            <div class="fluent-invalid-feedback">
                                {{ $errors->first('display_name') }}
                            </div>
                        @endif
                        @if(trans('cruds.permission.fields.display_name_helper'))
                            <small class="text-secondary">{{ trans('cruds.permission.fields.display_name_helper') }}</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="fluent-card-footer mt-4 p-0 border-0 bg-transparent justify-start">
                <button class="fluent-btn fluent-btn-primary" type="submit">
                    <i class="ri-save-line mr-1"></i>
                    {{ trans('global.save') }}
                </button>
                <a href="{{ route('admin.permissions.index') }}" class="fluent-btn fluent-btn-secondary">
                    <i class="ri-close-line mr-1"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
