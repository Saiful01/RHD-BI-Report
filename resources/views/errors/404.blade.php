@extends('layouts.error')

@section('title', '404 - Page Not Found')
@section('header-class', 'warning')
@section('icon-class', 'lost')
@section('code', '404')
@section('error-title', 'Road Not Found')
@section('message', 'Looks like you\'ve taken a wrong turn! The page you\'re looking for doesn\'t exist or has been moved to a different route.')

@section('icon')
<i class="ri-road-map-line"></i>
@endsection

@section('extra-content')
<div class="error-extra">
    <div class="error-extra-title">Possible reasons:</div>
    <p class="error-extra-text">
        The URL may be misspelled, the page may have been moved or deleted, or the link you followed may be outdated.
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
<a href="{{ url('/') }}" class="error-btn error-btn-secondary">
    <i class="ri-home-line"></i>
    Home
</a>
@endsection
