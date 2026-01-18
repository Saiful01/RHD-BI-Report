@extends('layouts.app')
@section('title', trans('global.reset_password') . ' - ' . trans('panel.site_title'))
@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="ri-lock-password-line"></i>
        </div>
        <h1 class="auth-title">{{ trans('global.reset_password') }}</h1>
        <p class="auth-subtitle">Create a new password for your account</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('password.request') }}">
            @csrf

            <input name="token" value="{{ $token }}" type="hidden">

            <div class="fluent-form-group">
                <label class="fluent-label" for="email">
                    <i class="ri-mail-line mr-1"></i>
                    {{ trans('global.login_email') }}
                </label>
                <div class="fluent-input-group">
                    <span class="fluent-input-icon">
                        <i class="ri-mail-line"></i>
                    </span>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="fluent-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                        autocomplete="email"
                        autofocus
                        placeholder="Enter your email"
                        value="{{ $email ?? old('email') }}"
                    >
                </div>
                @if($errors->has('email'))
                    <div class="fluent-invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <div class="fluent-form-group">
                <label class="fluent-label" for="password">
                    <i class="ri-lock-line mr-1"></i>
                    New {{ trans('global.login_password') }}
                </label>
                <div class="fluent-input-group">
                    <span class="fluent-input-icon">
                        <i class="ri-lock-line"></i>
                    </span>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="fluent-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        required
                        placeholder="Enter new password"
                    >
                </div>
                @if($errors->has('password'))
                    <div class="fluent-invalid-feedback">
                        {{ $errors->first('password') }}
                    </div>
                @endif
            </div>

            <div class="fluent-form-group">
                <label class="fluent-label" for="password-confirm">
                    <i class="ri-lock-2-line mr-1"></i>
                    {{ trans('global.login_password_confirmation') }}
                </label>
                <div class="fluent-input-group">
                    <span class="fluent-input-icon">
                        <i class="ri-lock-2-line"></i>
                    </span>
                    <input
                        id="password-confirm"
                        name="password_confirmation"
                        type="password"
                        class="fluent-input"
                        required
                        placeholder="Confirm new password"
                    >
                </div>
            </div>

            <div class="fluent-form-group mb-0">
                <button type="submit" class="fluent-btn fluent-btn-primary w-100 fluent-btn-lg">
                    <i class="ri-refresh-line mr-2"></i>
                    {{ trans('global.reset_password') }}
                </button>
            </div>
        </form>
    </div>

    <div class="auth-footer">
        <a href="{{ route('login') }}" class="text-primary">
            <i class="ri-arrow-left-line mr-1"></i>
            Back to Login
        </a>
    </div>
</div>
@endsection
