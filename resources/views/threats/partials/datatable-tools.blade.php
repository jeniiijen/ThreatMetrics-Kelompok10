<div class="card mb-4" style="background:var(--tm-panel, #111a2a); border:1px solid var(--tm-border, #26364b); border-radius:13px;">
    <div class="card-body">
        {{-- Filter row --}}
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-2">
                <label for="filter-date-from" class="form-label mb-1" style="font-size:11px;color:#8295ad;">From Date</label>
                <input type="date" id="filter-date-from" class="form-control form-control-sm"
                       style="background:#0c1625;color:#e8f1fc;border-color:#26364b;">
            </div>

            <div class="col-md-2">
                <label for="filter-date-to" class="form-label mb-1" style="font-size:11px;color:#8295ad;">To Date</label>
                <input type="date" id="filter-date-to" class="form-control form-control-sm"
                       style="background:#0c1625;color:#e8f1fc;border-color:#26364b;">
            </div>

            <div class="col-md-2">
                <label for="filter-type" class="form-label mb-1" style="font-size:11px;color:#8295ad;">IOC Type</label>
                <select id="filter-type" class="form-select form-select-sm"
                        style="background:#0c1625;color:#e8f1fc;border-color:#26364b;">
                    <option value="">All Types</option>
                    <option value="MD5">MD5</option>
                    <option value="DOMAIN">DOMAIN</option>
                    <option value="URL">URL</option>
                    <option value="IP">IP</option>
                    <option value="SHA256">SHA256</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="filter-tag" class="form-label mb-1" style="font-size:11px;color:#8295ad;">Tag</label>
                <input type="text" id="filter-tag" class="form-control form-control-sm"
                       placeholder="e.g., phishing"
                       style="background:#0c1625;color:#e8f1fc;border-color:#26364b;">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="button" id="btn-apply-dt-filter" class="btn btn-sm btn-primary px-3">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <button type="button" id="btn-reset-dt-filter" class="btn btn-sm btn-outline-secondary px-3">
                    Reset
                </button>
            </div>
        </div>

        {{-- Export & Import toolbar --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2"
             style="border-top:1px solid var(--tm-border, #26364b);">
            <div class="d-flex flex-wrap gap-2">
                <a href="#" id="btn-export-xlsx" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                </a>
                <a href="#" id="btn-export-csv" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-filetype-csv me-1"></i> Export CSV
                </a>
                <a href="#" id="btn-export-spss" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-file-earmark-zip me-1"></i> Export SPSS
                </a>
                <a href="#" id="btn-export-pdf" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </a>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('threatmetrics.import.template', ['format' => 'xlsx']) }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i> Template XLSX
                </a>
                <a href="{{ route('threatmetrics.import.template', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i> Template CSV
                </a>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-1"></i> Import Data
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Import modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background:#111a2a;color:#f2f6fc;border:1px solid #26364b;">
            <form action="{{ route('threatmetrics.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #26364b;">
                    <h5 class="modal-title" id="importModalLabel">Import Threat IOC Data</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="import_file" class="form-label">Select File (.xlsx or .csv)</label>
                    <input type="file" name="file" id="import_file" class="form-control" accept=".xlsx,.csv,.txt" required
                           style="background:#0c1625;color:#e8f1fc;border-color:#26364b;">
                </div>
                <div class="modal-footer" style="border-top:1px solid #26364b;">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload &amp; Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        #threats-table,
        #threats-table.dataTable {
            --bs-table-bg: #111a2a !important;
            --bs-table-striped-bg: #121d30 !important;
            --bs-table-hover-bg: #152235 !important;
            --bs-table-color: #f2f6fc !important;
            background-color: #111a2a !important;
            color: #f2f6fc !important;
            margin-bottom: 0 !important;
        }

        #threats-table thead th,
        #threats-table.dataTable thead th,
        #threats-table.dataTable thead td {
            background-color: #152235 !important;
            color: #8295ad !important;
            border-bottom: 1px solid #26364b !important;
        }

        #threats-table tbody tr,
        #threats-table.dataTable tbody tr {
            background-color: #111a2a !important;
            color: #f2f6fc !important;
            border-top: 1px solid #26364b !important;
        }

        #threats-table tbody tr:hover,
        #threats-table tbody tr:hover > td,
        #threats-table.dataTable tbody tr:hover,
        #threats-table.dataTable tbody tr:hover > td {
            background-color: #152235 !important;
        }

        #threats-table tbody td,
        #threats-table.dataTable tbody td {
            background-color: #111a2a !important;
            color: #f2f6fc !important;
            border-top: 1px solid #26364b !important;
        }

        #threats-table_wrapper .dataTables_filter input,
        #threats-table_wrapper .dataTables_length select,
        #threats-table_wrapper .dt-search input,
        #threats-table_wrapper .dt-length select {
            background-color: #0c1625 !important;
            color: #e8f1fc !important;
            border: 1px solid #26364b !important;
            border-radius: 6px;
            padding: 4px 8px;
        }

        #threats-table_wrapper .dataTables_info,
        #threats-table_wrapper .dataTables_length,
        #threats-table_wrapper .dataTables_filter,
        #threats-table_wrapper .dt-info,
        #threats-table_wrapper .dt-length,
        #threats-table_wrapper .dt-search {
            color: #8295ad !important;
            font-size: 12px;
            padding: 12px 16px;
        }

        #threats-table_wrapper .pagination .page-link,
        #threats-table_wrapper .dt-paging .dt-paging-button {
            background-color: #111a2a !important;
            color: #a4b5ca !important;
            border-color: #26364b !important;
            font-size: 12px;
        }

        #threats-table_wrapper .pagination .page-item.active .page-link,
        #threats-table_wrapper .dt-paging .dt-paging-button.current,
        #threats-table_wrapper .dt-paging .dt-paging-button.current:hover {
            background-color: #13c9dc !important;
            border-color: #13c9dc !important;
            color: #0b1120 !important;
            font-weight: 700;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function ensureDataTables(callback) {
                if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
                    callback(window.jQuery);
                    return;
                }

                function loadScript(src, next) {
                    var s = document.createElement('script');
                    s.src = src;
                    s.onload = next;
                    s.onerror = next;
                    document.body.appendChild(s);
                }

                if (!window.jQuery) {
                    loadScript('https://code.jquery.com/jquery-3.7.1.min.js', function () {
                        loadScript('https://cdn.datatables.net/2.1.8/js/dataTables.min.js', function () {
                            loadScript('https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js', function () {
                                if (window.jQuery) callback(window.jQuery);
                            });
                        });
                    });
                } else {
                    loadScript('https://cdn.datatables.net/2.1.8/js/dataTables.min.js', function () {
                        loadScript('https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js', function () {
                            callback(window.jQuery);
                        });
                    });
                }
            }

            ensureDataTables(function ($) {
                $(function () {
                    const dataUrl = @json(route('threatmetrics.data'));
                    const exportBaseUrl = @json(url('/threats/export'));
                    const pdfExportUrl = @json(url('/threats/export/pdf'));
                    const showBaseUrl = @json(url('/threatmetrics'));
                    const noticeEl = document.getElementById('apiRefreshNotice');

                    $.fn.dataTable.ext.errMode = 'none';

                    function getFilterParams() {
                        return {
                            date_from: $('#filter-date-from').val() || '',
                            date_to: $('#filter-date-to').val() || '',
                            type: $('#filter-type').val() || '',
                            tag: $('#filter-tag').val() || ''
                        };
                    }

                    function syncExportLinks() {
                        const params = new URLSearchParams(getFilterParams());
                        const queryString = params.toString() ? ('?' + params.toString()) : '';

                        $('#btn-export-xlsx').attr('href', exportBaseUrl + '/xlsx' + queryString);
                        $('#btn-export-csv').attr('href', exportBaseUrl + '/csv' + queryString);
                        $('#btn-export-spss').attr('href', exportBaseUrl + '/spss' + queryString);
                        $('#btn-export-pdf').attr('href', pdfExportUrl + queryString);
                    }

                    function escapeHtml(text) {
                        return $('<div>').text(text == null ? '' : String(text)).html();
                    }

                    function getTypeBadgeStyle(typeName) {
                        const cleanType = String(typeName || '').toUpperCase().trim();
                        switch (cleanType) {
                            case 'IP':
                                return 'background:#44391f;color:#f5ca52;';
                            case 'DOMAIN':
                                return 'background:#123e36;color:#37dfa5;';
                            case 'URL':
                                return 'background:#18334b;color:#54b8ff;';
                            case 'MD5':
                            case 'SHA256':
                            case 'HASH':
                                return 'background:#47273b;color:#ff7199;';
                            default:
                                return 'background:#102e40;color:#13c9dc;';
                        }
                    }

                    syncExportLinks();

                    if ($.fn.dataTable.isDataTable('#threats-table')) {
                        $('#threats-table').DataTable().clear().destroy();
                    }

                    const table = $('#threats-table').DataTable({
                        processing: true,
                        serverSide: true,
                        pageLength: 10,
                        order: [[1, 'desc']],
                        ajax: {
                            url: dataUrl,
                            type: 'GET',
                            data: function (d) {
                                const filters = getFilterParams();
                                d.date_from = filters.date_from;
                                d.date_to = filters.date_to;
                                d.type = filters.type;
                                d.tag = filters.tag;
                            },
                            error: function (xhr) {
                                if (noticeEl) {
                                    noticeEl.className = 'tm-refresh-notice error';
                                    noticeEl.style.display = 'block';
                                    noticeEl.textContent = 'Gagal memuat data IOC dari server (HTTP ' + (xhr.status || 'Error') + ').';
                                }
                            }
                        },
                        columns: [
                            {
                                data: 'threat_id',
                                name: 'threat_id',
                                className: 'px-4 py-3',
                                render: function (data, type, row) {
                                    const displayId = data || ('TH-' + String(row.id || 0).padStart(4, '0'));
                                    return '<span class="fw-bold" style="color:#13c9dc;">' + escapeHtml(displayId) + '</span>';
                                }
                            },
                            {
                                data: 'date',
                                name: 'date',
                                className: 'px-3 py-3',
                                render: function (data) {
                                    return '<span style="color:#8295ad;font-size:13px;">' + escapeHtml(data || '-') + '</span>';
                                }
                            },
                            {
                                data: 'user',
                                name: 'user',
                                className: 'px-3 py-3 fw-semibold',
                                render: function (data) {
                                    return '<span style="color:#f2f6fc;">' + escapeHtml(data || '-') + '</span>';
                                }
                            },
                            {
                                data: 'type',
                                name: 'type',
                                className: 'px-3 py-3',
                                render: function (data) {
                                    const typeText = String(data || 'UNKNOWN').toUpperCase();
                                    const badgeStyle = getTypeBadgeStyle(typeText);
                                    return '<span class="badge rounded-pill px-3 py-2" style="' + badgeStyle + '">' + escapeHtml(typeText) + '</span>';
                                }
                            },
                            {
                                data: 'value',
                                name: 'value',
                                className: 'px-3 py-3 fw-semibold',
                                render: function (data) {
                                    return '<span style="color:#f2f6fc;font-size:13px;word-break:break-all;">' + escapeHtml(data || '') + '</span>';
                                }
                            },
                            {
                                data: 'tags',
                                name: 'tags',
                                orderable: false,
                                className: 'px-3 py-3',
                                render: function (tags) {
                                    if (!Array.isArray(tags) || tags.length === 0) {
                                        return '<span style="color:#8295ad;">-</span>';
                                    }
                                    return tags.map(function (tag) {
                                        const clean = String(tag).replace(/^#+/, '').trim();
                                        if (!clean) return '';
                                        return '<span class="badge rounded-pill px-2 py-1 me-1" style="background:#18334b;color:#72c6ff;font-size:11px;">' + escapeHtml(clean) + '</span>';
                                    }).join('');
                                }
                            },
                            {
                                data: null,
                                name: 'action',
                                orderable: false,
                                searchable: false,
                                className: 'px-3 py-3 text-center',
                                render: function (data, type, row) {
                                    const id = row.id;
                                    const displayId = row.threat_id || ('TH-' + String(id || 0).padStart(4, '0'));
                                    const label = displayId + ' (' + (row.value || '') + ')';
                                    const showUrl = row.show_url || (showBaseUrl + '/' + id);
                                    const editUrl = row.edit_url || (showBaseUrl + '/' + id + '/edit');
                                    const destroyUrl = row.destroy_url || (showBaseUrl + '/' + id);

                                    return (
                                        '<div class="d-flex justify-content-center gap-2">' +
                                            '<a href="' + escapeHtml(showUrl) + '" class="btn btn-sm" style="background:#102e40;color:#13c9dc;border-radius:7px;" title="View">' +
                                                '<i class="bi bi-eye"></i>' +
                                            '</a>' +
                                            '<a href="' + escapeHtml(editUrl) + '" class="btn btn-sm" style="background:#44391f;color:#f5ca52;border-radius:7px;" title="Edit">' +
                                                '<i class="bi bi-pencil-square"></i>' +
                                            '</a>' +
                                            '<button type="button" class="btn btn-sm tm-trigger-delete" style="background:#482631;color:#ff7180;border-radius:7px;" data-url="' + escapeHtml(destroyUrl) + '" data-label="' + escapeHtml(label) + '" title="Delete">' +
                                                '<i class="bi bi-trash"></i>' +
                                            '</button>' +
                                        '</div>'
                                    );
                                }
                            }
                        ]
                    });

                    $('#btn-apply-dt-filter').on('click', function () {
                        syncExportLinks();
                        table.ajax.reload();
                    });

                    $('#btn-reset-dt-filter').on('click', function () {
                        $('#filter-date-from').val('');
                        $('#filter-date-to').val('');
                        $('#filter-type').val('');
                        $('#filter-tag').val('');
                        syncExportLinks();
                        table.ajax.reload();
                    });

                    $('#filter-date-from, #filter-date-to, #filter-type, #filter-tag').on('change', function () {
                        syncExportLinks();
                    });
                });
            });
        })();
    </script>
@endpush