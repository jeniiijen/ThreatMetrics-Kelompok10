@extends('layouts.app')

@section('title', 'Add Threat | ThreatMetrics')

@section('content')
<div class="container-fluid py-2" style="max-width: 960px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <div class="text-uppercase fw-bold" style="color: var(--cyan); font-size: 11px; letter-spacing: 2px;">
                Indicator Management
            </div>
            <h1 class="h3 fw-bold text-white mb-1 mt-1">Add New Threat Indicator</h1>
            <p class="text-secondary mb-0" style="font-size: 13px;">
                Input manual IOC record into the ThreatMetrics database.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="card">
        <div class="card-header py-3 px-4">
            <h5 class="mb-0 fw-bold text-white" style="font-size: 14px;">
                <i class="bi bi-shield-plus me-2" style="color: var(--cyan);"></i>
                Threat Record Form
            </h5>
        </div>

        <div class="card-body p-4">
            {{-- Safely check $errors so pages rendered without session error bag never throw Undefined variable --}}
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger mb-4">
                    <div class="fw-bold mb-1">Please fix the following errors:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('threatmetrics.store') }}" method="POST">
                @csrf

                @include('threats.partials.form')

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--border);">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light px-4">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Save Threat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection