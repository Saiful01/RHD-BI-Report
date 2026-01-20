@extends('layouts.error')

@section('title', '503 - Service Unavailable')
@section('header-class', 'maintenance')
@section('icon-class', 'spin')
@section('code', '503')
@section('error-title', 'Under Maintenance')
@section('message', 'We\'re currently performing scheduled maintenance to improve our services. We\'ll be back shortly!')

@section('icon')
<i class="ri-tools-line"></i>
@endsection

@section('extra-content')
<div class="error-extra">
    <div class="error-extra-title">Scheduled Maintenance</div>
    <p class="error-extra-text">
        We're making improvements to serve you better. This typically takes a few minutes. Thank you for your patience!
    </p>
</div>
@endsection

@section('actions')
<button onclick="location.reload()" class="error-btn error-btn-warning">
    <i class="ri-refresh-line"></i>
    Check Again
</button>
@endsection

@section('scripts')
<script>
    // Auto-refresh every 30 seconds
    setTimeout(() => {
        location.reload();
    }, 30000);
</script>
@endsection
