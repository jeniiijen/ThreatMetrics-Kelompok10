@extends('layouts.guest')

@section('title', 'Register')

@section('content')

<div class="mb-4">
    <div class="text-uppercase mb-2"
         style="color:#13c9dc;font-size:10px;font-weight:800;letter-spacing:2px;">
        CREATE YOUR ACCOUNT
    </div>

    <h2 class="auth-heading">Join ThreatMetrics</h2>

    <p class="auth-description">
        Buat akun untuk mengakses platform Threat Intelligence.
    </p>
</div>

@if($errors->any())
    <div class="alert alert-danger auth-alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('register.store') }}" method="POST">

    @csrf

    <div class="mb-3">
        <label for="name" class="form-label auth-label">
            Full Name
        </label>

        <input
            type="text"
            name="name"
            id="name"
            class="form-control auth-input"
            placeholder="Enter your full name"
            value="{{ old('name') }}"
            autocomplete="name"
            maxlength="100"
            required
        >

        @error('name')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

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
                placeholder="Minimum 8 characters"
                autocomplete="new-password"
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

        @error('password')
            <div class="auth-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation"
               class="form-label auth-label">
            Confirm Password
        </label>

        <div class="auth-password-wrap">
            <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="form-control auth-input"
                placeholder="Re-enter your password"
                autocomplete="new-password"
                required
            >

            <button
                type="button"
                class="auth-toggle"
                data-password-toggle="password_confirmation"
            >
                Show
            </button>
        </div>
    </div>

    <button type="submit" class="auth-button">
        <i class="bi bi-person-plus me-2"></i>
        CREATE ACCOUNT
    </button>

</form>

<div class="auth-divider"></div>

<div class="text-center">
    <p class="auth-small mb-0">
        Sudah memiliki akun?

        <a href="{{ route('login') }}" class="auth-link">
            Sign In
        </a>
    </p>
</div>

@endsection
