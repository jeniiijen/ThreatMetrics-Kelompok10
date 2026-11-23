@extends('layouts.app')

@section('title', 'Add Threat - ThreatMetrics')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white mb-1">Add New Threat</h3>
            <p class="text-secondary mb-0">
                Add a new threat intelligence record.
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
            <h6 class="mb-0">
                <i class="bi bi-shield-plus me-2 text-info"></i>
                Threat Information
            </h6>
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

            <form action="{{ route('threatmetrics.store') }}"
                  method="POST">

                @csrf

                @include('threats.partials.form')

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('threatmetrics.index') }}"
                       class="btn btn-outline-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>
                        Add Threat
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
