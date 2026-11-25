{{--
    Anggota 4: toolbar filter, ekspor/impor, laporan status impor, dan DataTables.
    Di-include dari threats/index.blade.php. Tabel yang dipakai: #threats-table.
--}}

@php
    $importReport = session('import_report');
@endphp

{{-- LAPORAN HASIL IMPOR --}}
@if($importReport)
    @php
        $summary = $importReport['summary'];
        $statusStyles = [
            'berhasil' => 'background-color:#174b43; color:#00d69b;',
            'ditolak' => 'background-color:#514b26; color:#f4d52d;',
            'tidak valid' => 'background-color:#482b3a; color:#ff3155;',
        ];
    @endphp

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="text-white fw-bold mb-0">Import Report</h5>

            <div class="d-flex flex-wrap gap-2">
                <span class="badge rounded-pill px-3 py-2" style="{{ $statusStyles['berhasil'] }}">
                    Berhasil: {{ $summary['berhasil'] }}
                </span>
                <span class="badge rounded-pill px-3 py-2" style="{{ $statusStyles['ditolak'] }}">
                    Ditolak: {{ $summary['ditolak'] }}
                </span>
                <span class="badge rounded-pill px-3 py-2" style="{{ $statusStyles['tidak valid'] }}">
                    Tidak valid: {{ $summary['tidak valid'] }}
                </span>
                <span class="badge rounded-pill px-3 py-2" style="background:#263b4e; color:#9bcff0;">
                    Total baris: {{ $summary['total'] }}
                </span>
            </div>
        </div>

        @if(count($importReport['details']) > 0)
            <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-3">Baris</th>
                            <th class="px-3">Status</th>
                            <th class="px-3">Tipe</th>
                            <th class="px-3">Value</th>
                            <th class="px-3">Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($importReport['details'] as $detail)
                            <tr>
                                <td class="px-3">{{ $detail['row'] }}</td>
                                <td class="px-3">
                                    <span class="badge rounded-pill px-3 py-2"
                                          style="{{ $statusStyles[$detail['status']] ?? '' }}">
                                        {{ $detail['status'] }}
                                    </span>
                                </td>
                                <td class="px-3">{{ $detail['type'] ?: '-' }}</td>
                                <td class="px-3" style="word-break:break-all;">{{ $detail['value'] ?: '-' }}</td>
                                <td class="px-3">{{ $detail['reason'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($importReport['hidden_details'] > 0)
                <div class="card-body py-2 text-secondary" style="font-size:13px;">
                    + {{ $importReport['hidden_details'] }} baris bermasalah lainnya tidak ditampilkan.
                </div>
            @endif
        @else
            <div class="card-body text-secondary">
                Semua baris berhasil diimpor.
            </div>
        @endif
    </div>
@endif

{{-- TOOLBAR: FILTER, EKSPOR, IMPOR --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">

            <div class="col-6 col-md-2">
                <label for="filter-type" class="form-label">Type</label>
                <select id="filter-type" class="form-select form-select-sm">
                    <option value="">All types</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label for="filter-date-from" class="form-label">Date from</label>
                <input type="date" id="filter-date-from" class="form-control form-control-sm">
            </div>

            <div class="col-6 col-md-2">
                <label for="filter-date-to" class="form-label">Date to</label>
                <input type="date" id="filter-date-to" class="form-control form-control-sm">
            </div>

            <div class="col-6 col-md-2">
                <button type="button" id="filter-reset" class="btn btn-sm btn-outline-light w-100">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
            </div>

            <div class="col-12 col-md-4 d-flex justify-content-md-end gap-2">

                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li>
                            <a class="dropdown-item js-export" href="#"
                               data-url="{{ route('threatmetrics.export', ['format' => 'xlsx']) }}">
                                Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item js-export" href="#"
                               data-url="{{ route('threatmetrics.export', ['format' => 'csv']) }}">
                                CSV (.csv)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item js-export" href="#"
                               data-url="{{ route('threatmetrics.export', ['format' => 'spss']) }}">
                                SPSS (.zip: CSV + syntax)
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 py-1 text-secondary" style="font-size:12px;">
                            Mengikuti pencarian &amp; filter aktif
                        </li>
                    </ul>
                </div>

                @if(auth()->user()->isAdmin())
                    <button type="button"
                            class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#importModal">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- MODAL IMPOR --}}
@if(auth()->user()->isAdmin())
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content"
              method="POST"
              action="{{ route('threatmetrics.import') }}"
              enctype="multipart/form-data"
              style="background:#1e2b37; border:1px solid #304252; color:#f0f4f8;">
            @csrf

            <div class="modal-header" style="border-bottom:1px solid #405364;">
                <h5 class="modal-title" id="importModalLabel">Import Threat Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <label for="import-file" class="form-label">File .xlsx atau .csv (maks. 5 MB, {{ \App\Services\ThreatImportService::MAX_ROWS }} baris)</label>
                <input type="file"
                       id="import-file"
                       name="file"
                       class="form-control"
                       accept=".xlsx,.csv"
                       required>

                <div class="mt-3 text-secondary" style="font-size:13px;">
                    <div class="mb-1">Kolom wajib: <code>date</code>, <code>user</code>, <code>type</code>, <code>value</code>. Kolom opsional: <code>tags</code>, <code>tweet</code>.</div>
                    <div class="mb-1">Tipe yang diterima: {{ implode(', ', \App\Services\ThreatImportService::ALLOWED_TYPES) }}.</div>
                    <div class="mb-1">Kolom <code>threat_id</code> diabaikan; ID dibuat otomatis.</div>
                    <ul class="mb-0 ps-3">
                        <li><b>berhasil</b>: baris disimpan.</li>
                        <li><b>ditolak</b>: baris valid tetapi duplikat (tanggal + user + tipe + value sama).</li>
                        <li><b>tidak valid</b>: ada isian yang salah format atau kosong.</li>
                    </ul>
                </div>

                <div class="mt-3" style="font-size:13px;">
                    Template:
                    <a href="{{ route('threatmetrics.import.template', ['format' => 'xlsx']) }}">.xlsx</a>
                    &middot;
                    <a href="{{ route('threatmetrics.import.template', ['format' => 'csv']) }}">.csv</a>
                </div>
            </div>

            <div class="modal-footer" style="border-top:1px solid #405364;">
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

@endif

@push('styles')
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* Tema gelap untuk DataTables */
        .dt-container {
            color: var(--muted);
        }

        .dt-container .row {
            margin: 0;
            padding: 12px 16px;
        }

        .dt-container .dt-info,
        .dt-container .dt-length label,
        .dt-container .dt-search label {
            color: var(--muted);
            font-size: 13px;
        }

        .dt-container .dt-search input,
        .dt-container .dt-length select {
            margin: 0 6px;
        }

        .dt-container table.dataTable {
            --bs-table-striped-bg: transparent;
            --bs-table-striped-color: var(--text);
            width: 100% !important;
            border-collapse: collapse;
        }

        .dt-container table.dataTable > tbody > tr > * {
            box-shadow: none;
            border-top: 1px solid #304252;
        }

        .dt-container table.dataTable > thead > tr > th {
            border-bottom: 0;
            box-shadow: none;
        }

        .dt-container .dt-processing {
            background: var(--panel-header) !important;
            color: var(--text) !important;
            border: 1px solid var(--border);
        }

        .modal .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
    </style>
@endpush

@push('scripts')
    {{-- Muat jQuery hanya jika belum dimuat (mis. oleh Anggota 5 untuk Quill) --}}
    <script>
        window.jQuery || document.write('<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"><\/script>');
    </script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        (function () {
            const tableElement = document.getElementById('threats-table');

            if (!tableElement || !window.jQuery || !jQuery.fn.DataTable) {
                return; // DataTables gagal dimuat: tabel Blade tetap tampil seperti semula.
            }

            const csrfToken = @json(csrf_token());
            const dataUrl = @json(route('threatmetrics.data'));

            const typeStyles = {
                MD5: 'background-color:#482b3a; color:#ff3155;',
                DOMAIN: 'background-color:#174b43; color:#00d69b;',
                URL: 'background-color:#19435e; color:#159be8;',
                IP: 'background-color:#514b26; color:#f4d52d;',
            };
            const defaultTypeStyle = 'background-color:#263b4e; color:#8da4bb;';

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[char]));

            const typeSelect = document.getElementById('filter-type');
            const dateFrom = document.getElementById('filter-date-from');
            const dateTo = document.getElementById('filter-date-to');

            const table = jQuery(tableElement).DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'desc']],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: dataUrl,
                    data: (params) => {
                        params.type = typeSelect.value;
                        params.date_from = dateFrom.value;
                        params.date_to = dateTo.value;
                    },
                },
                columns: [
                    {
                        data: 'threat_id',
                        render: (data, type) => type === 'display'
                            ? `<span class="fw-bold" style="color:#159be8;">${escapeHtml(data)}</span>`
                            : data,
                    },
                    {
                        data: 'date',
                        render: (data, type) => type === 'display'
                            ? `<span style="color:#9aafc1; font-size:13px;">${escapeHtml(data)}</span>`
                            : data,
                    },
                    {
                        data: 'user',
                        render: (data, type) => type === 'display'
                            ? `<span class="text-white fw-semibold">${escapeHtml(data)}</span>`
                            : data,
                    },
                    {
                        data: 'type',
                        render: (data, type) => {
                            if (type !== 'display') {
                                return data;
                            }
                            const style = typeStyles[data] || defaultTypeStyle;
                            return `<span class="badge rounded-pill px-3 py-2" style="${style} font-weight:600; letter-spacing:0.5px;">${escapeHtml(data) || '-'}</span>`;
                        },
                    },
                    {
                        data: 'value',
                        render: (data, type) => type === 'display'
                            ? `<span class="text-white fw-semibold" style="font-size:13px; word-break:break-all;">${escapeHtml(data)}</span>`
                            : data,
                    },
                    {
                        data: 'tags',
                        orderable: false,
                        render: (data, type) => {
                            if (type !== 'display') {
                                return (data || []).join(', ');
                            }
                            if (!data || data.length === 0) {
                                return '<span style="color:#71869a;">-</span>';
                            }
                            const badges = data.map((tag) =>
                                `<span class="badge rounded-pill px-2 py-2" style="background:#19435e; color:#64baff; font-size:11px;">${escapeHtml(String(tag).trim())}</span>`
                            ).join('');
                            return `<div class="d-flex flex-wrap gap-1">${badges}</div>`;
                        },
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (data, type, row) => {
                            if (type !== 'display') {
                                return '';
                            }
                            return `
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="${escapeHtml(row.show_url)}" class="btn btn-sm" title="View"
                                       style="background:#19435e; color:#159be8; border-radius:7px;">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="${escapeHtml(row.edit_url)}" class="btn btn-sm" title="Edit"
                                       style="background:#514b26; color:#f4d52d; border-radius:7px;">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="${escapeHtml(row.pdf_url)}" class="btn btn-sm" title="Download PDF"
                                       style="background:#3a1f2b; color:#ff6b8a; border-radius:7px;">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <form action="${escapeHtml(row.destroy_url)}" method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-sm" title="Delete"
                                                style="background:#482b3a; color:#ff3155; border-radius:7px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>`;
                        },
                    },
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(disaring dari _MAX_ total data)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Belum ada data ancaman',
                    processing: 'Memuat...',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // Paginasi Laravel digantikan paginasi DataTables.
            const laravelFooter = tableElement.closest('.card')?.querySelector('.card-footer');
            if (laravelFooter) {
                laravelFooter.style.setProperty('display', 'none', 'important');
            }

            // Isi dropdown tipe dari respons server (sekali, mempertahankan pilihan).
            let typesLoaded = false;
            table.on('xhr.dt', (event, settings, json) => {
                if (typesLoaded || !json || !Array.isArray(json.types)) {
                    return;
                }
                typesLoaded = true;
                json.types.forEach((type) => {
                    const option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    typeSelect.appendChild(option);
                });
            });

            [typeSelect, dateFrom, dateTo].forEach((input) => {
                input.addEventListener('change', () => table.draw());
            });

            document.getElementById('filter-reset').addEventListener('click', () => {
                typeSelect.value = '';
                dateFrom.value = '';
                dateTo.value = '';
                table.search('').draw();
            });

            // Ekspor mengikuti pencarian dan filter yang sedang aktif.
            document.querySelectorAll('.js-export').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();

                    const params = new URLSearchParams();
                    const filters = {
                        search: table.search(),
                        type: typeSelect.value,
                        date_from: dateFrom.value,
                        date_to: dateTo.value,
                    };

                    Object.entries(filters).forEach(([key, value]) => {
                        if (value) {
                            params.set(key, value);
                        }
                    });

                    const query = params.toString();
                    window.location.href = link.dataset.url + (query ? '?' + query : '');
                });
            });
        })();
    </script>
@endpush
