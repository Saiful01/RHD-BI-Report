@extends('layouts.error')

@section('title', '401 - Unauthorized')
@section('header-class', 'info')
@section('icon-class', 'bounce')
@section('code', '401')
@section('error-title', 'Authentication Required')
@section('message', 'You need to be logged in to access this page. Please sign in with your credentials to continue.')

@section('icon')
<i class="ri-lock-line"></i>
@endsection

@section('actions')
<a href="{{ route('login') }}" class="error-btn error-btn-primary">
    <i class="ri-login-box-line"></i>
    Sign In
</a>
<a href="{{ url('/') }}" class="error-btn error-btn-secondary">
    <i class="ri-home-line"></i>
    Go Home
</a>
@endsection
