@extends('layouts.app')

@section('title', 'Edit Threat - ThreatMetrics')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white mb-1">Edit Threat</h3>
            <p class="text-secondary mb-0">
                Update threat intelligence information.
            </p>
        </div>

        <a href="{{ route('threatmetrics.index') }}"
           class="btn btn-outline-light">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>
    </div>

    <div class="card shadow-sm">

        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-pencil-square me-2 text-info"></i>
                    Edit Threat Information
                </h6>

                <span class="badge"
                      style="background:#263b4e;color:#159be8;">
                    {{ $threat->threat_id }}
                </span>
            </div>
        </div>

        <div class="card-body p-4">

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Please check your input:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('threatmetrics.update', ['threatmetric' => $threat->id]) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Threat ID</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $threat->threat_id }}"
                           disabled>
                    <small class="text-secondary">
                        Threat ID is automatically generated and cannot be changed.
                    </small>
                </div>

                @include('threats.partials.form')

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('threatmetrics.index') }}"
                       class="btn btn-outline-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Save Changes
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
