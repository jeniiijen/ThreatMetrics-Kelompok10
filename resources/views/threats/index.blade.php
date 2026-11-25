@extends('layouts.app')

@section('content')

@php
    $threats = $threats ?? collect();
    $errorBag = isset($errors) ? $errors : (session('errors') ?? new \Illuminate\Support\ViewErrorBag());
@endphp

<div class="container-fluid px-4 py-4">

    {{-- HEADER HALAMAN --}}
    <div class="mb-4">
        <h2 class="text-white fw-bold mb-1">Threat Intelligence</h2>
        <p class="text-secondary mb-0">
            Manage and monitor threat indicators
        </p>
    </div>

    {{-- ALERT NOTIFIKASI SUKSES --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ALERT NOTIFIKASI ERROR --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- ALERT VALIDATION ERRORS --}}
    @if($errorBag->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errorBag->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ANGGOTA 4: laporan impor, filter, ekspor/impor, dan skrip DataTables --}}
    @include('threats.partials.datatable-tools')

    {{-- CARD UTAMA TABEL THREAT RECORDS --}}
    <div class="card"
         style="background:#1e2b37; border:1px solid #304252; border-radius:12px; overflow:hidden;">

        {{-- HEADER CARD & TOMBOL ADD THREAT --}}
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

        {{-- TABEL DATA THREAT --}}
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

                        @php
                            $threatId = data_get($threat, 'id');
                            $threatCode = data_get($threat, 'threat_id')
                                ?? ($threatId ? 'TH-' . str_pad($threatId, 4, '0', STR_PAD_LEFT) : '-');
                            $threatDate = data_get($threat, 'date');
                            $threatUser = data_get($threat, 'user', '-');
                            $threatValue = data_get($threat, 'value', '-');
                        @endphp

                        <tr style="background:#1e2b37; border-top:1px solid #304252;">

                            {{-- ID THREAT --}}
                            <td class="px-4 py-3">
                                <span class="fw-bold" style="color:#159be8;">
                                    {{ $threatCode }}
                                </span>
                            </td>

                            {{-- TANGGAL & WAKTU --}}
                            <td class="px-3 py-3">
                                <span style="color:#9aafc1; font-size:13px;">
                                    {{ $threatDate ? \Carbon\Carbon::parse($threatDate)->format('d M Y, H:i') : '-' }}
                                </span>
                            </td>

                            {{-- USER --}}
                            <td class="px-3 py-3">
                                <span class="text-white fw-semibold">
                                    {{ $threatUser }}
                                </span>
                            </td>

                            {{-- BADGE TIPE IOC --}}
                            <td class="px-3 py-3">

                                @php
                                    $typeColors = [
                                        'MD5' => 'background-color:#482b3a; color:#ff3155;',
                                        'DOMAIN' => 'background-color:#174b43; color:#00d69b;',
                                        'URL' => 'background-color:#19435e; color:#159be8;',
                                        'IP' => 'background-color:#514b26; color:#f4d52d;',
                                    ];

                                    $type = strtoupper((string) data_get($threat, 'type', ''));

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
                                    {{ $threatValue }}
                                </span>
                            </td>

                            {{-- TAGS --}}
                            <td class="px-3 py-3">

                                @php
                                    $tags = data_get($threat, 'tags');

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
                                        @if(trim((string) $tag) !== '')
                                            <span class="badge rounded-pill px-2 py-2"
                                                  style="background:#19435e; color:#64baff; font-size:11px;">
                                                {{ trim((string) $tag) }}
                                            </span>
                                        @endif
                                    @empty
                                        <span style="color:#71869a;">-</span>
                                    @endforelse

                                </div>

                            </td>

                            {{-- ACTION BUTTONS --}}
                            <td class="px-3 py-3 text-center">

                                <div class="d-flex justify-content-center gap-2">

                                    {{-- SHOW --}}
                                    @if(!empty($threatId))
                                        <a href="{{ route('threatmetrics.show', ['threatmetric' => $threatId]) }}"
                                           class="btn btn-sm"
                                           title="View"
                                           style="background:#19435e; color:#159be8; border-radius:7px;">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif

                                    {{-- EDIT --}}
                                    @if(!empty($threatId))
                                        <a href="{{ route('threatmetrics.edit', ['threatmetric' => $threatId]) }}"
                                           class="btn btn-sm"
                                           title="Edit"
                                           style="background:#514b26; color:#f4d52d; border-radius:7px;">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif

                                    {{-- PDF (Anggota 5): hanya dirender jika $threatId valid --}}
                                    @if(!empty($threatId))
                                        <a href="{{ route('threatmetrics.pdf', ['threatmetric' => $threatId]) }}"
                                           class="btn btn-sm"
                                           title="Download PDF"
                                           style="background:#3a1f2b; color:#ff6b8a; border-radius:7px;">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    @endif

                                    {{-- DELETE --}}
                                    @if(!empty($threatId))
                                        <form action="{{ route('threatmetrics.destroy', ['threatmetric' => $threatId]) }}"
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
                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        {{-- DATA KOSONG --}}
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

{{-- STYLE DARK THEME & PAGINATION --}}
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