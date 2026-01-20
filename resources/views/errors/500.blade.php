@extends('layouts.error')

@section('title', '500 - Server Error')
@section('header-class', '')
@section('icon-class', 'pulse')
@section('code', '500')
@section('error-title', 'Something Went Wrong')
@section('message', 'We encountered an unexpected error while processing your request. Our team has been notified and is working to fix the issue.')

@section('icon')
<i class="ri-error-warning-line"></i>
@endsection

@section('extra-content')
<div class="error-extra">
    <div class="error-extra-title">What can you do?</div>
    <p class="error-extra-text">
        Try refreshing the page, or come back later. If the problem persists, please contact the system administrator.
    </p>
</div>
@endsection

@section('actions')
<button onclick="location.reload()" class="error-btn error-btn-primary">
    <i class="ri-refresh-line"></i>
    Try Again
</button>
<a href="{{ url('/admin') }}" class="error-btn error-btn-secondary">
    <i class="ri-dashboard-line"></i>
    Dashboard
</a>
<a href="{{ url('/') }}" class="error-btn error-btn-secondary">
    <i class="ri-home-line"></i>
    Home
</a>
@endsection
