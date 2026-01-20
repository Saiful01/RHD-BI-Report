@extends('layouts.error')

@section('title', '419 - Page Expired')
@section('header-class', 'warning')
@section('icon-class', 'spin')
@section('code', '419')
@section('error-title', 'Session Expired')
@section('message', 'Your session has expired due to inactivity. This is a security measure to protect your account. Please refresh the page and try again.')

@section('icon')
<i class="ri-timer-line"></i>
@endsection

@section('extra-content')
<div class="error-extra">
    <div class="error-extra-title">Why did this happen?</div>
    <p class="error-extra-text">
        For security reasons, sessions expire after a period of inactivity. Simply refresh the page to get a new session token.
    </p>
</div>
@endsection

@section('actions')
<button onclick="location.reload()" class="error-btn error-btn-primary">
    <i class="ri-refresh-line"></i>
    Refresh Page
</button>
<a href="{{ route('login') }}" class="error-btn error-btn-secondary">
    <i class="ri-login-box-line"></i>
    Sign In Again
</a>
@endsection
