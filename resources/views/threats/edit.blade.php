@extends('layouts.app')

@section('content')

@php
    $record = $threatmetric ?? $threat ?? null;
    $recordId = data_get($record, 'id');
    $errorBag = isset($errors) ? $errors : (session('errors') ?? new \Illuminate\Support\ViewErrorBag());
    $existingTags = data_get($record, 'tags');

    if (is_array($existingTags)) {
        $tagsText = implode(', ', $existingTags);
    } elseif (is_string($existingTags)) {
        $decodedTags = json_decode($existingTags, true);
        $tagsText = is_array($decodedTags) ? implode(', ', $decodedTags) : $existingTags;
    } else {
        $tagsText = '';
    }
@endphp

<div class="container-fluid px-4 py-4">

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">Edit Threat</h2>
            <p class="text-secondary mb-0">Perbarui data indikator ancaman</p>
        </div>

        <a href="{{ \Illuminate\Support\Facades\Route::has('threatmetrics.index') ? route('threatmetrics.index') : route('dashboard') }}"
           class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card"
         style="background:#1e2b37; border:1px solid #304252; border-radius:12px;">

        <div class="card-body p-4">

            {{-- Validation errors --}}
            @if($errorBag->any())
                <div class="alert alert-danger">
                    <strong>Please check your input:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errorBag->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($recordId))
                <form action="{{ route('threatmetrics.update', ['threatmetric' => $recordId]) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="user" class="form-label text-white">User</label>
                            <input type="text"
                                   id="user"
                                   name="user"
                                   value="{{ old('user', data_get($record, 'user')) }}"
                                   class="form-control bg-dark text-white border-secondary"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label text-white">Type</label>
                            @php
                                $currentType = strtoupper((string) old('type', data_get($record, 'type', 'IP')));
                            @endphp
                            <select id="type"
                                    name="type"
                                    class="form-select bg-dark text-white border-secondary"
                                    required>
                                <option value="IP" {{ $currentType === 'IP' ? 'selected' : '' }}>IP</option>
                                <option value="DOMAIN" {{ $currentType === 'DOMAIN' ? 'selected' : '' }}>DOMAIN</option>
                                <option value="URL" {{ $currentType === 'URL' ? 'selected' : '' }}>URL</option>
                                <option value="MD5" {{ $currentType === 'MD5' ? 'selected' : '' }}>MD5</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label for="value" class="form-label text-white">Indicator / Value</label>
                            <input type="text"
                                   id="value"
                                   name="value"
                                   value="{{ old('value', data_get($record, 'value')) }}"
                                   class="form-control bg-dark text-white border-secondary"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label for="date" class="form-label text-white">Date Time</label>
                            @php
                                $rawDate = old('date', data_get($record, 'date'));
                                $formattedDate = $rawDate
                                    ? \Carbon\Carbon::parse($rawDate)->format('Y-m-d\TH:i')
                                    : '';
                            @endphp
                            <input type="datetime-local"
                                   id="date"
                                   name="date"
                                   value="{{ $formattedDate }}"
                                   class="form-control bg-dark text-white border-secondary">
                        </div>

                        <div class="col-12">
                            <label for="tags" class="form-label text-white">Tags (pisahkan dengan koma)</label>
                            <input type="text"
                                   id="tags"
                                   name="tags"
                                   value="{{ old('tags', $tagsText) }}"
                                   placeholder="contoh: phishing, malware, c2"
                                   class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>

                    {{-- Form actions --}}
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit"
                                class="btn px-4"
                                style="background:#1688d4; color:white; font-weight:600;">
                            Save Changes
                        </button>

                        <a href="{{ route('threatmetrics.show', ['threatmetric' => $recordId]) }}"
                           class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            @else
                <div class="alert alert-warning mb-0">Data threat tidak ditemukan.</div>
            @endif

        </div>
    </div>

</div>

<style>
    body {
        background-color: #17232f !important;
    }
</style>

@endsection