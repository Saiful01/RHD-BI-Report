@extends('layouts.app')
@section('title', 'Login - ' . trans('panel.site_title'))
@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="ri-road-map-line"></i>
        </div>
        <h1 class="auth-title">{{ trans('panel.site_title') }}</h1>
        <p class="auth-subtitle">Sign in to your account</p>
    </div>

    <div class="auth-body">
        @if(session('message'))
            <div class="fluent-alert fluent-alert-info mb-4">
                <span class="fluent-alert-icon">
                    <i class="ri-information-line"></i>
                </span>
                <div class="fluent-alert-content">
                    {{ session('message') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="fluent-form-group">
                <label class="fluent-label" for="email">
                    <i class="ri-mail-line mr-1"></i>
                    {{ trans('global.login_email') }}
                </label>
                <div class="fluent-input-group">
                    <span class="fluent-input-icon">
                        <i class="ri-user-line"></i>
                    </span>
                    <input
                        id="email"
                        name="email"
                        type="text"
                        class="fluent-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        required
                        autocomplete="email"
                        autofocus
                        placeholder="Enter your email"
                        value="{{ old('email', null) }}"
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
                    {{ trans('global.login_password') }}
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
                        placeholder="Enter your password"
                    >
                </div>
                @if($errors->has('password'))
                    <div class="fluent-invalid-feedback">
                        {{ $errors->first('password') }}
                    </div>
                @endif
            </div>

            <div class="fluent-form-group">
                <label class="fluent-checkbox-label">
                    <input type="checkbox" name="remember" id="remember">
                    <span>{{ trans('global.remember_me') }}</span>
                </label>
            </div>

            <div class="fluent-form-group mb-0">
                <button type="submit" class="fluent-btn fluent-btn-primary w-100 fluent-btn-lg login-btn" id="loginBtn">
                    <span class="btn-text">
                        <i class="ri-login-box-line mr-2"></i>
                        {{ trans('global.login') }}
                    </span>
                    <span class="btn-loader">
                        <svg class="spinner" viewBox="0 0 24 24" width="20" height="20">
                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-linecap="round">
                                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/>
                            </circle>
                        </svg>
                        <span>Signing in...</span>
                    </span>
                </button>
            </div>
        </form>

<style>
.login-btn {
    position: relative;
}
.login-btn .btn-text,
.login-btn .btn-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}
.login-btn .btn-loader {
    display: none;
}
.login-btn.loading .btn-text {
    display: none;
}
.login-btn.loading .btn-loader {
    display: flex;
}
.login-btn.loading {
    opacity: 0.85;
    pointer-events: none;
}
.login-btn .spinner {
    flex-shrink: 0;
}
</style>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('loginBtn').classList.add('loading');
});
</script>
    </div>

    @if(Route::has('password.request'))
        <div class="auth-footer">
            <a href="{{ route('password.request') }}" class="text-primary">
                <i class="ri-key-line mr-1"></i>
                {{ trans('global.forgot_password') }}
            </a>
        </div>
    @endif
</div>
@endsection
