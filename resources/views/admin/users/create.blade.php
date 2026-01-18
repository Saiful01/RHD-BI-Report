@extends('layouts.admin')
@section('content')
<div class="fluent-page-header">
    <h1 class="fluent-page-title">
        <span class="fluent-page-title-icon">
            <i class="ri-user-add-line"></i>
        </span>
        {{ trans('global.create') }} {{ trans('cruds.user.title_singular') }}
    </h1>
    <div class="fluent-page-actions">
        <a href="{{ route('admin.users.index') }}" class="fluent-btn fluent-btn-secondary">
            <i class="ri-arrow-left-line"></i>
            {{ trans('global.back_to_list') }}
        </a>
    </div>
</div>

<div class="fluent-card">
    <div class="fluent-card-header">
        <h3 class="fluent-card-title">
            <i class="ri-information-line fluent-card-title-icon"></i>
            User Information
        </h3>
    </div>

    <div class="fluent-card-body">
        <form method="POST" action="{{ route("admin.users.store") }}" enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <div class="form-col">
                    <div class="fluent-form-group">
                        <label class="fluent-label fluent-label-required" for="name">
                            <i class="ri-user-line mr-1"></i>{{ trans('cruds.user.fields.name') }}
                        </label>
                        <input class="fluent-input {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required placeholder="Enter full name">
                        @if($errors->has('name'))
                            <div class="fluent-invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        @if(trans('cruds.user.fields.name_helper'))
                            <small class="text-secondary">{{ trans('cruds.user.fields.name_helper') }}</small>
                        @endif
                    </div>
                </div>

                <div class="form-col">
                    <div class="fluent-form-group">
                        <label class="fluent-label fluent-label-required" for="email">
                            <i class="ri-mail-line mr-1"></i>{{ trans('cruds.user.fields.email') }}
                        </label>
                        <input class="fluent-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="Enter email address">
                        @if($errors->has('email'))
                            <div class="fluent-invalid-feedback">
                                {{ $errors->first('email') }}
                            </div>
                        @endif
                        @if(trans('cruds.user.fields.email_helper'))
                            <small class="text-secondary">{{ trans('cruds.user.fields.email_helper') }}</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="fluent-form-group">
                        <label class="fluent-label fluent-label-required" for="password">
                            <i class="ri-lock-line mr-1"></i>{{ trans('cruds.user.fields.password') }}
                        </label>
                        <input class="fluent-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" id="password" required placeholder="Enter password">
                        @if($errors->has('password'))
                            <div class="fluent-invalid-feedback">
                                {{ $errors->first('password') }}
                            </div>
                        @endif
                        @if(trans('cruds.user.fields.password_helper'))
                            <small class="text-secondary">{{ trans('cruds.user.fields.password_helper') }}</small>
                        @endif
                    </div>
                </div>

                <div class="form-col">
                    <div class="fluent-form-group">
                        <label class="fluent-label fluent-label-required" for="roles">
                            <i class="ri-shield-user-line mr-1"></i>{{ trans('cruds.user.fields.roles') }}
                        </label>
                        <div class="mb-2">
                            <span class="fluent-btn fluent-btn-sm fluent-btn-ghost select-all">
                                <i class="ri-checkbox-multiple-line"></i> {{ trans('global.select_all') }}
                            </span>
                            <span class="fluent-btn fluent-btn-sm fluent-btn-ghost deselect-all">
                                <i class="ri-checkbox-blank-line"></i> {{ trans('global.deselect_all') }}
                            </span>
                        </div>
                        <select class="fluent-select select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}" name="roles[]" id="roles" multiple required>
                            @foreach($roles as $id => $role)
                                <option value="{{ $id }}" {{ in_array($id, old('roles', [])) ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('roles'))
                            <div class="fluent-invalid-feedback">
                                {{ $errors->first('roles') }}
                            </div>
                        @endif
                        @if(trans('cruds.user.fields.roles_helper'))
                            <small class="text-secondary">{{ trans('cruds.user.fields.roles_helper') }}</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="fluent-card-footer mt-4 p-0 border-0 bg-transparent justify-start">
                <button class="fluent-btn fluent-btn-primary" type="submit">
                    <i class="ri-save-line mr-1"></i>
                    {{ trans('global.save') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="fluent-btn fluent-btn-secondary">
                    <i class="ri-close-line mr-1"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
