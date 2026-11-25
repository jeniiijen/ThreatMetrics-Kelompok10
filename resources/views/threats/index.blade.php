
@extends('layouts.app')

@section('content')

<div class="container-fluid px-4 py-4">

    {{-- HEADER --}}
    <div class="mb-4">
        <h2 class="text-white fw-bold mb-1">Threat Intelligence</h2>
        <p class="text-secondary mb-0">
            Manage and monitor threat indicators
        </p>
    </div>

    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ALERT ERROR --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ANGGOTA 4: laporan impor, filter, ekspor/impor, dan skrip DataTables --}}
    @include('threats.partials.datatable-tools')

    {{-- TABLE CARD --}}
    <div class="card"
         style="background:#1e2b37; border:1px solid #304252; border-radius:12px; overflow:hidden;">

        {{-- TABLE HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#263543; border-bottom:1px solid #405364;">

            <h5 class="text-white fw-bold mb-0">
                Threat Records
            </h5>

            <a href="{{ route('threatmetrics.create') }}"
               class="btn rounded-pill px-4"
               style="background:#1688d4; color:white; font-weight:600;">
                + Add Threat
            </a>

        </div>

        {{-- TABLE --}}
        <div class="table-responsive">

            <table id="threats-table" class="table table-borderless align-middle mb-0">

                <thead style="background:#263543;">

                    <tr class="text-uppercase"
                        style="font-size:12px; letter-spacing:0.7px; color:#9aafc1;">

                        <th class="px-4 py-3">ID</th>
                        <th class="px-3 py-3">Date Time</th>
                        <th class="px-3 py-3">User</th>
                        <th class="px-3 py-3">Type</th>
                        <th class="px-3 py-3">Indicator / Value</th>
                        <th class="px-3 py-3">Tags</th>
                        <th class="px-3 py-3 text-center">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($threats as $threat)

                        <tr style="background:#1e2b37; border-top:1px solid #304252;">

                            {{-- ID --}}
                            <td class="px-4 py-3">
                                <span class="fw-bold" style="color:#159be8;">
                                    {{ $threat->threat_id ?? 'TH-' . str_pad($threat->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            {{-- DATE TIME --}}
                            <td class="px-3 py-3">
                                <span style="color:#9aafc1; font-size:13px;">
                                    {{ $threat->date ? \Carbon\Carbon::parse($threat->date)->format('d M Y, H:i') : '-' }}
                                </span>
                            </td>

                            {{-- USER --}}
                            <td class="px-3 py-3">
                                <span class="text-white fw-semibold">
                                    {{ $threat->user }}
                                </span>
                            </td>

                            {{-- TYPE BADGE --}}
                            <td class="px-3 py-3">

                                @php
                                    $typeColors = [
                                        'MD5' => 'background-color:#482b3a; color:#ff3155;',
                                        'DOMAIN' => 'background-color:#174b43; color:#00d69b;',
                                        'URL' => 'background-color:#19435e; color:#159be8;',
                                        'IP' => 'background-color:#514b26; color:#f4d52d;',
                                    ];

                                    $type = strtoupper($threat->type ?? '');

                                    $typeStyle = $typeColors[$type]
                                        ?? 'background-color:#263b4e; color:#8da4bb;';
                                @endphp

                                <span class="badge rounded-pill px-3 py-2"
                                      style="{{ $typeStyle }} font-weight:600; letter-spacing:0.5px;">
                                    {{ $type ?: '-' }}
                                </span>

                            </td>

                            {{-- INDICATOR / VALUE --}}
                            <td class="px-3 py-3">
                                <span class="text-white fw-semibold"
                                      style="font-size:13px; word-break:break-all;">
                                    {{ $threat->value }}
                                </span>
                            </td>

                            {{-- TAGS --}}
                            <td class="px-3 py-3">

                                @php
                                    $tags = $threat->tags;

                                    if (is_string($tags)) {
                                        $decodedTags = json_decode($tags, true);
                                        $tags = is_array($decodedTags)
                                            ? $decodedTags
                                            : explode(',', $tags);
                                    }

                                    if (!is_array($tags)) {
                                        $tags = [];
                                    }
                                @endphp

                                <div class="d-flex flex-wrap gap-1">

                                    @forelse($tags as $tag)

                                        <span class="badge rounded-pill px-2 py-2"
                                              style="background:#19435e; color:#64baff; font-size:11px;">
                                            {{ trim($tag) }}
                                        </span>

                                    @empty

                                        <span style="color:#71869a;">-</span>

                                    @endforelse

                                </div>

                            </td>

                            {{-- ACTION --}}
                            <td class="px-3 py-3 text-center">

                                <div class="d-flex justify-content-center gap-2">

                                    {{-- SHOW --}}
                                    <a href="{{ route('threatmetrics.show', ['threatmetric' => $threat->id]) }}"
                                       class="btn btn-sm"
                                       title="View"
                                       style="background:#19435e; color:#159be8; border-radius:7px;">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- EDIT --}}
                                    <a href="{{ route('threatmetrics.edit', ['threatmetric' => $threat->id]) }}"
                                       class="btn btn-sm"
                                       title="Edit"
                                       style="background:#514b26; color:#f4d52d; border-radius:7px;">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    {{-- PDF (Anggota 5) --}}
                                    <a href="{{ route('threatmetrics.pdf', ['threatmetric' => $threat->id]) }}"
                                       class="btn btn-sm"
                                       title="Download PDF"
                                       style="background:#3a1f2b; color:#ff6b8a; border-radius:7px;">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                    {{-- DELETE --}}
                                    <form action="{{ route('threatmetrics.destroy', ['threatmetric' => $threat->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm"
                                                title="Delete"
                                                style="background:#482b3a; color:#ff3155; border-radius:7px;">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr style="background:#1e2b37;">
                            <td colspan="7"
                                class="text-center py-5"
                                style="color:#9aafc1;">
                                No threat records found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        @if(method_exists($threats, 'links'))

            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2"
                 style="background:#263543; border-top:1px solid #405364;">

                <span style="color:#9aafc1; font-size:13px;">
                    Showing {{ $threats->firstItem() ?? 0 }}
                    to {{ $threats->lastItem() ?? 0 }}
                    of {{ $threats->total() }} records
                </span>

                {{ $threats->links('pagination::bootstrap-5') }}

            </div>

        @endif

    </div>

</div>

<style>
    body {
        background-color: #17232f !important;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #263543 !important;
    }

    .page-link {
        background-color: #1e2b37;
        color: #159be8;
        border-color: #405364;
    }

    .page-link:hover {
        background-color: #263543;
        color: white;
    }

    .page-item.active .page-link {
        background-color: #1688d4;
        border-color: #1688d4;
    }
</style>

@endsection
