@extends('layouts.error')

@section('title', '403 - Forbidden')
@section('header-class', '')
@section('icon-class', 'shake')
@section('code', '403')
@section('error-title', 'Access Denied')
@section('message', 'You don\'t have permission to access this resource. If you believe this is a mistake, please contact your administrator.')

@section('icon')
<i class="ri-shield-cross-line"></i>
@endsection

@section('extra-content')
<div class="error-extra">
    <div class="error-extra-title">What can you do?</div>
    <p class="error-extra-text">
        Contact your system administrator to request access, or return to a page you have permission to view.
    </p>
</div>
@endsection

@section('actions')
<button onclick="goBack()" class="error-btn error-btn-secondary">
    <i class="ri-arrow-left-line"></i>
    Go Back
</button>
<a href="{{ url('/admin') }}" class="error-btn error-btn-primary">
    <i class="ri-dashboard-line"></i>
    Dashboard
</a>
@endsection
