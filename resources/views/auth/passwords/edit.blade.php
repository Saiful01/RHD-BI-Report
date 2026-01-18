@extends('layouts.admin')
@section('title', trans('global.change_password') . ' - ' . trans('panel.site_title'))
@section('content')
<style>
    .profile-page {
        max-width: 1000px;
        margin: 0 auto;
    }

    .profile-header {
        background: linear-gradient(135deg, #0078D4 0%, #106EBE 100%);
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 24px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 600;
        color: #fff;
        border: 3px solid rgba(255, 255, 255, 0.3);
    }

    .profile-info h1 {
        margin: 0 0 4px 0;
        font-size: 24px;
        font-weight: 600;
    }

    .profile-info p {
        margin: 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .profile-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }

    .profile-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #E1DFDD;
        overflow: hidden;
    }

    .profile-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #E1DFDD;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .profile-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-card-icon.blue {
        background: linear-gradient(135deg, #0078D4 0%, #106EBE 100%);
        color: #fff;
    }

    .profile-card-icon.orange {
        background: linear-gradient(135deg, #FFB900 0%, #FF8C00 100%);
        color: #fff;
    }

    .profile-card-icon.red {
        background: linear-gradient(135deg, #D13438 0%, #A4262C 100%);
        color: #fff;
    }

    .profile-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #323130;
        margin: 0;
    }

    .profile-card-body {
        padding: 24px;
    }

    .profile-form-group {
        margin-bottom: 20px;
    }

    .profile-form-group:last-child {
        margin-bottom: 0;
    }

    .profile-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #323130;
        margin-bottom: 8px;
    }

    .profile-label.required::after {
        content: ' *';
        color: #D13438;
    }

    .profile-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #8A8886;
        border-radius: 6px;
        font-size: 14px;
        color: #323130;
        background: #fff;
        transition: all 0.2s ease;
    }

    .profile-input:focus {
        outline: none;
        border-color: #0078D4;
        box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.1);
    }

    .profile-input.is-invalid {
        border-color: #D13438;
    }

    .profile-input.is-invalid:focus {
        box-shadow: 0 0 0 2px rgba(209, 52, 56, 0.1);
    }

    .invalid-feedback {
        color: #D13438;
        font-size: 12px;
        margin-top: 6px;
    }

    .profile-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .profile-btn-primary {
        background: linear-gradient(135deg, #0078D4 0%, #106EBE 100%);
        color: #fff;
    }

    .profile-btn-primary:hover {
        background: linear-gradient(135deg, #106EBE 0%, #0078D4 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);
    }

    .profile-btn-warning {
        background: linear-gradient(135deg, #FFB900 0%, #FF8C00 100%);
        color: #fff;
    }

    .profile-btn-warning:hover {
        background: linear-gradient(135deg, #FF8C00 0%, #FFB900 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 185, 0, 0.3);
    }

    .profile-btn-danger {
        background: linear-gradient(135deg, #D13438 0%, #A4262C 100%);
        color: #fff;
    }

    .profile-btn-danger:hover {
        background: linear-gradient(135deg, #A4262C 0%, #D13438 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(209, 52, 56, 0.3);
    }

    .delete-warning {
        background: #FEF0F0;
        border: 1px solid #FDE7E9;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .delete-warning-icon {
        color: #D13438;
        font-size: 20px;
        margin-right: 12px;
    }

    .delete-warning-text {
        color: #A4262C;
        font-size: 13px;
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            padding: 24px;
        }

        .profile-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-page">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="profile-info">
            <h1>{{ auth()->user()->name }}</h1>
            <p>{{ auth()->user()->email }}</p>
        </div>
    </div>

    <div class="profile-cards">
        <!-- My Profile Card -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div class="profile-card-icon blue">
                    <i class="ri-user-3-line"></i>
                </div>
                <h2 class="profile-card-title">{{ trans('global.my_profile') }}</h2>
            </div>
            <div class="profile-card-body">
                <form method="POST" action="{{ route("profile.password.updateProfile") }}">
                    @csrf
                    <div class="profile-form-group">
                        <label class="profile-label required" for="name">{{ trans('cruds.user.fields.name') }}</label>
                        <input
                            class="profile-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', auth()->user()->name) }}"
                            required
                        >
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                    </div>
                    <div class="profile-form-group">
                        <label class="profile-label required" for="email">{{ trans('cruds.user.fields.email') }}</label>
                        <input
                            class="profile-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            required
                        >
                        @if($errors->has('email'))
                            <div class="invalid-feedback">
                                {{ $errors->first('email') }}
                            </div>
                        @endif
                    </div>
                    <div class="profile-form-group">
                        <button class="profile-btn profile-btn-primary" type="submit">
                            <i class="ri-save-line"></i>
                            {{ trans('global.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div class="profile-card-icon orange">
                    <i class="ri-lock-password-line"></i>
                </div>
                <h2 class="profile-card-title">{{ trans('global.change_password') }}</h2>
            </div>
            <div class="profile-card-body">
                <form method="POST" action="{{ route("profile.password.update") }}">
                    @csrf
                    <div class="profile-form-group">
                        <label class="profile-label required" for="password">New {{ trans('cruds.user.fields.password') }}</label>
                        <input
                            class="profile-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter new password"
                            required
                        >
                        @if($errors->has('password'))
                            <div class="invalid-feedback">
                                {{ $errors->first('password') }}
                            </div>
                        @endif
                    </div>
                    <div class="profile-form-group">
                        <label class="profile-label required" for="password_confirmation">Confirm New {{ trans('cruds.user.fields.password') }}</label>
                        <input
                            class="profile-input"
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            placeholder="Confirm new password"
                            required
                        >
                    </div>
                    <div class="profile-form-group">
                        <button class="profile-btn profile-btn-warning" type="submit">
                            <i class="ri-key-line"></i>
                            {{ trans('global.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Account Card -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div class="profile-card-icon red">
                    <i class="ri-delete-bin-line"></i>
                </div>
                <h2 class="profile-card-title">{{ trans('global.delete_account') }}</h2>
            </div>
            <div class="profile-card-body">
                <div class="delete-warning">
                    <i class="ri-error-warning-line delete-warning-icon"></i>
                    <span class="delete-warning-text">
                        This action cannot be undone. All your data will be permanently deleted.
                    </span>
                </div>
                <form method="POST" action="{{ route("profile.password.destroyProfile") }}" onsubmit="return prompt('{{ __('global.delete_account_warning') }}') == '{{ auth()->user()->email }}'">
                    @csrf
                    <div class="profile-form-group">
                        <button class="profile-btn profile-btn-danger" type="submit">
                            <i class="ri-delete-bin-line"></i>
                            {{ trans('global.delete') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
