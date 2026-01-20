@extends('layouts.error')

@section('title', '429 - Too Many Requests')
@section('header-class', 'warning')
@section('icon-class', 'shake')
@section('code', '429')
@section('error-title', 'Slow Down!')
@section('message', 'You\'ve made too many requests in a short period. Please wait a moment before trying again.')

@section('icon')
<i class="ri-speed-line"></i>
@endsection

@section('extra-content')
<div class="countdown-container">
    <div class="countdown-label">You can try again in:</div>
    <div class="countdown-timer" id="countdown">60</div>
</div>
@endsection

@section('actions')
<button onclick="location.reload()" class="error-btn error-btn-primary" id="retryBtn" disabled>
    <i class="ri-refresh-line"></i>
    <span id="retryText">Please Wait...</span>
</button>
<a href="{{ url('/admin') }}" class="error-btn error-btn-secondary">
    <i class="ri-dashboard-line"></i>
    Dashboard
</a>
@endsection

@section('scripts')
<script>
    let seconds = 60;
    const countdown = document.getElementById('countdown');
    const retryBtn = document.getElementById('retryBtn');
    const retryText = document.getElementById('retryText');

    const timer = setInterval(() => {
        seconds--;
        countdown.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(timer);
            countdown.textContent = '0';
            retryBtn.disabled = false;
            retryText.textContent = 'Try Again';
        }
    }, 1000);
</script>
@endsection
