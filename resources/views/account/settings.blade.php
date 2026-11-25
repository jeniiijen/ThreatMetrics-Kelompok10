@extends('layouts.app')

@section('title', 'Pengaturan Akun | ThreatMetrics')

@section('content')
    <div class="container px-0" style="max-width: 640px;">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light mb-3">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Dashboard
        </a>
        <h1 class="h3 mb-4">Pengaturan Akun</h1>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <section class="card mb-3" aria-labelledby="profile-heading">
            <h2 id="profile-heading" class="card-header h6 py-3">Profil</h2>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-secondary">Nama</dt>
                    <dd class="col-sm-9 text-break">{{ $user->name }}</dd>
                    <dt class="col-sm-3 text-secondary">Email</dt>
                    <dd class="col-sm-9 text-break">{{ $user->email }}</dd>
                    <dt class="col-sm-3 text-secondary">Role</dt>
                    <dd class="col-sm-9 mb-0">
                        <span class="badge text-bg-secondary">{{ ucfirst($user->role) }}</span>
                    </dd>
                </dl>
            </div>
        </section>

        <section class="card mb-3" aria-labelledby="password-heading">
            <h2 id="password-heading" class="card-header h6 py-3">Ubah Kata Sandi</h2>
            <form class="card-body" method="POST" action="{{ route('account.password.update') }}">
                @csrf
                @method('PUT')

                @foreach ([
                    'current_password' => 'Kata sandi saat ini',
                    'password' => 'Kata sandi baru',
                    'password_confirmation' => 'Konfirmasi kata sandi baru',
                ] as $field => $label)
                    <div class="mb-3">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="password"
                               class="form-control @error($field) is-invalid @enderror"
                               autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}"
                               @if ($field !== 'current_password') minlength="8" @endif
                               @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror
                               required>
                        @error($field)
                            <div id="{{ $field }}-error" class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">Simpan Kata Sandi</button>
            </form>
        </section>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Logout
            </button>
        </form>
    </div>
@endsection
