@extends('layouts.app')
@section('title', __('Register') . ' - ' . trans('panel.site_title'))
@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="ri-user-add-line"></i>
        </div>
        <h1 class="auth-title">{{ __('Register') }}</h1>
        <p class="auth-subtitle">Create a new account</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="fluent-form-group">
                <label class="fluent-label" for="name">
                    <i class="ri-user-line mr-1"></i>
                    {{ __('Name') }}
                </label>
                <div class="fluent-input-group">
                    <span class="fluent-input-icon">
                        <i class="ri-user-line"></i>
                    </span>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="fluent-input @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        autofocus
                        placeholder="Enter your full name"
                    >
                </div>
                @error('name')
                    <div class="fluent-invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="fluent-form-group">
                <label class="fluent-label" for="email">
                    <i class="ri-mail-line mr-1"></i>
                    {{ __('Email Address') }}
                </label>
                <div class="fluent-input-group">
                    <span class="fluent-input-icon">
                        <i class="ri-mail-line"></i>
                    </span>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="fluent-input @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="Enter your email"
                    >
                </div>
                @error('email')
                    <div class="fluent-invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="fluent-form-group">
                <label class="fluent-label" for="password">
                    <i class="ri-lock-line mr-1"></i>
                    {{ __('Password') }}
                </label>
                <div class="fluent-input-group">
                    <span class="fluent-input-icon">
                        <i class="ri-lock-line"></i>
                    </span>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="fluent-input @error('password') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="Enter your password"
                    >
                </div>
                @error('password')
                    <div class="fluent-invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="fluent-form-group">
                <label class="fluent-label" for="password-confirm">
                    <i class="ri-lock-2-line mr-1"></i>
                    {{ __('Confirm Password') }}
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
                        autocomplete="new-password"
                        placeholder="Confirm your password"
                    >
                </div>
            </div>

            <div class="fluent-form-group mb-0">
                <button type="submit" class="fluent-btn fluent-btn-primary w-100 fluent-btn-lg">
                    <i class="ri-user-add-line mr-2"></i>
                    {{ __('Register') }}
                </button>
            </div>
        </form>
    </div>

    <div class="auth-footer">
        <span>Already have an account?</span>
        <a href="{{ route('login') }}" class="text-primary ml-1">
            <i class="ri-login-box-line mr-1"></i>
            Login
        </a>
    </div>
</div>
@endsection
