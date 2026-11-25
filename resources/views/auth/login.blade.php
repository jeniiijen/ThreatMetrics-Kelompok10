@extends('layouts.guest')

@section('title', 'Login')

@section('content')

<div class="mb-4">
    <div class="text-uppercase mb-2"
         style="color:#13c9dc;font-size:10px;font-weight:800;letter-spacing:2px;">
        SECURE AUTHENTICATION
    </div>

    <h2 class="auth-heading">Welcome Back</h2>

    <p class="auth-description">
        Masuk ke akun ThreatMetrics untuk melanjutkan.
    </p>
</div>

@if(session('success'))
    <div class="alert alert-success auth-alert">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger auth-alert">
        {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('login.store') }}" method="POST">

    @csrf

    <div class="mb-3">
        <label for="email" class="form-label auth-label">
            Email Address
        </label>

        <input
            type="email"
            name="email"
            id="email"
            class="form-control auth-input"
            placeholder="name@example.com"
            value="{{ old('email') }}"
            autocomplete="email"
            required
            autofocus
        >

        @error('email')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label auth-label">
            Password
        </label>

        <div class="auth-password-wrap">
            <input
                type="password"
                name="password"
                id="password"
                class="form-control auth-input"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >

            <button
                type="button"
                class="auth-toggle"
                data-password-toggle="password"
            >
                Show
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <label class="auth-small d-flex align-items-center gap-2">
            <input
                type="checkbox"
                name="remember"
                value="1"
            >
            Remember me
        </label>

        <span class="auth-small">
            Secure Login
        </span>

    </div>

    <button type="submit" class="auth-button">
        <i class="bi bi-box-arrow-in-right me-2"></i>
        SIGN IN
    </button>

</form>

<div class="auth-divider"></div>

<div class="text-center">
    <p class="auth-small mb-0">
        Belum memiliki akun?

        <a href="{{ route('register') }}" class="auth-link">
            Create Account
        </a>
    </p>
</div>

@endsection
