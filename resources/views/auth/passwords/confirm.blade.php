@extends('layouts.app')
@section('title', __('Confirm Password') . ' - ' . trans('panel.site_title'))
@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="ri-shield-keyhole-line"></i>
        </div>
        <h1 class="auth-title">{{ __('Confirm Password') }}</h1>
        <p class="auth-subtitle">{{ __('Please confirm your password before continuing.') }}</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

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
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                </div>
                @error('password')
                    <div class="fluent-invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="fluent-form-group mb-0">
                <button type="submit" class="fluent-btn fluent-btn-primary w-100 fluent-btn-lg">
                    <i class="ri-checkbox-circle-line mr-2"></i>
                    {{ __('Confirm Password') }}
                </button>
            </div>
        </form>
    </div>

    @if(Route::has('password.request'))
        <div class="auth-footer">
            <a href="{{ route('password.request') }}" class="text-primary">
                <i class="ri-key-line mr-1"></i>
                {{ __('Forgot Your Password?') }}
            </a>
        </div>
    @endif
</div>
@endsection
