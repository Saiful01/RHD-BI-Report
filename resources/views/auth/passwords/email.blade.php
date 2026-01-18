@extends('layouts.app')
@section('title', trans('global.reset_password') . ' - ' . trans('panel.site_title'))
@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="ri-key-line"></i>
        </div>
        <h1 class="auth-title">{{ trans('global.reset_password') }}</h1>
        <p class="auth-subtitle">Enter your email to receive a password reset link</p>
    </div>

    <div class="auth-body">
        @if(session('status'))
            <div class="fluent-alert fluent-alert-success mb-4">
                <span class="fluent-alert-icon">
                    <i class="ri-checkbox-circle-line"></i>
                </span>
                <div class="fluent-alert-content">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

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
                        placeholder="Enter your email address"
                        value="{{ old('email') }}"
                    >
                </div>
                @if($errors->has('email'))
                    <div class="fluent-invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <div class="fluent-form-group mb-0">
                <button type="submit" class="fluent-btn fluent-btn-primary w-100 fluent-btn-lg">
                    <i class="ri-mail-send-line mr-2"></i>
                    {{ trans('global.send_password') }}
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
