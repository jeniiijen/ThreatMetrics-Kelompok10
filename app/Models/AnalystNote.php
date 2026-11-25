{{-- Database-backed anonymous analyst notes with Quill Editor and jQuery --}}
<div class="mt-4" id="analyst-notes-section">
    <div class="card" style="background:var(--tm-panel, #111a2a); border:1px solid var(--tm-border, #26364b); border-radius:13px;">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:var(--tm-panel2, #152235); border-bottom:1px solid var(--tm-border, #26364b);">
            <div>
                <h5 class="mb-0 text-white fw-bold" style="font-size:14px;">
                    <i class="bi bi-chat-square-quote me-2" style="color:var(--tm-cyan, #13c9dc);"></i>
                    Analyst Notes &amp; Quick Discussion
                </h5>
                <div class="text-secondary" style="font-size:11px;">
                    Catatan tim anonim dengan nama panggilan acak yang muncul di setiap postingan
                </div>
            </div>
            <span class="badge rounded-pill px-3 py-2" style="background:#102e40; color:#13c9dc; font-size:10px;">
                QUILL + JQUERY + DB
            </span>
        </div>

        <div class="card-body">
            {{-- Anonymous nickname bar with random generator --}}
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-5">
                    <label for="tm-anon-name" class="form-label mb-1" style="font-size:11px;">
                        Nama panggilan kamu
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" style="background:#152235; border-color:#26364b; color:#13c9dc;">
                            <i class="bi bi-incognito"></i>
                        </span>
                        <input type="text"
                               id="tm-anon-name"
                               class="form-control"
                               maxlength="40"
                               placeholder="Nama panggilan lucu...">
                        <button type="button" id="tm-random-name" class="btn btn-outline-info" title="Acak nama baru">
                            <i class="bi bi-shuffle"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-7 text-md-end">
                    <small class="text-secondary" style="font-size:11px;">
                        Tersimpan di database ThreatMetrics
                    </small>
                </div>
            </div>

            {{-- Quill WYSIWYG editor container --}}
            <div id="tm-quill-editor"
                 style="min-height:120px; background:#0c1625; color:#f2f6fc; border:1px solid #26364b; border-bottom-left-radius:8px; border-bottom-right-radius:8px;">
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small id="tm-note-feedback" class="text-secondary" style="font-size:12px;"></small>
                <div class="d-flex gap-2">
                    <button type="button" id="tm-clear-editor" class="btn btn-sm btn-outline-light">
                        Bersihkan
                    </button>
                    <button type="button" id="tm-post-note" class="btn btn-sm btn-primary px-3">
                        <i class="bi bi-send me-1"></i> Kirim Catatan
                    </button>
                </div>
            </div>

            <hr class="my-4" style="border-color:#26364b;">

            {{-- Posted anonymous notes feed --}}
            <div id="tm-notes-list" class="d-flex flex-column gap-3"></div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        #analyst-notes-section .ql-toolbar.ql-snow {
            background: #152235;
            border-color: #26364b;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        #analyst-notes-section .ql-toolbar.ql-snow .ql-stroke {
            stroke: #a5b4c8;
        }
        #analyst-notes-section .ql-toolbar.ql-snow .ql-fill {
            fill: #a5b4c8;
        }
        #analyst-notes-section .ql-toolbar.ql-snow .ql-picker-label {
            color: #a5b4c8;
        }
        #analyst-notes-section .ql-container.ql-snow {
            border-color: #26364b;
            font-family: inherit;
            font-size: 13px;
        }
        #analyst-notes-section .ql-editor.ql-blank::before {
            color: #8295ad;
            font-style: normal;
        }
        .tm-note-card {
            background: #0c1625;
            border: 1px solid #26364b;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .tm-note-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            background: #102e40;
            color: #13c9dc;
            flex-shrink: 0;
        }
        .tm-note-body p:last-child {
            margin-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <script>
        jQuery(function ($) {
            const csrfToken = @json(csrf_token());
            const indexUrl = @json(route('analyst-notes.index'));
            const storeUrl = @json(route('analyst-notes.store'));
            const destroyBaseUrl = @json(url('/analyst-notes'));
            const storageKeyName = 'threatmetrics_anon_name_v1';

            const funnyNames = [
                'Penyambung Dimensi',
                'Penjaga Log Malam',
                'Pemburu Hash Nyasar',
                'Pengamat Paket Liar',
                'Kapten Phishing Radar',
                'Petualang Subnet',
                'Penjinak Payload',
                'Tukang Intip Header'
            ];

            function pickRandomName() {
                const idx = Math.floor(Math.random() * funnyNames.length);
                return funnyNames[idx];
            }

            let savedName = localStorage.getItem(storageKeyName);
            if (!savedName || !savedName.trim()) {
                savedName = pickRandomName();
                localStorage.setItem(storageKeyName, savedName);
            }
            $('#tm-anon-name').val(savedName);

            // Initialize Quill WYSIWYG editor
            const quill = new Quill('#tm-quill-editor', {
                theme: 'snow',
                placeholder: 'Tulis catatan analisis IOC, temuan mencurigakan, atau obrolan santai tim...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'code-block', 'link'],
                        ['clean']
                    ]
                }
            });

            function escapeText(str) {
                return $('<div>').text(str || '').html();
            }

            function renderNotesList(notes) {
                const $list = $('#tm-notes-list');
                $list.empty();

                if (!Array.isArray(notes) || notes.length === 0) {
                    $list.append(
                        '<div class="text-center text-secondary py-3" style="font-size:12px;">Belum ada catatan. Mulai tulis catatan pertamamu di atas.</div>'
                    );
                    return;
                }

                notes.forEach(function (item) {
                    const initial = (item.author || 'A').trim().charAt(0).toUpperCase() || 'A';
                    const cardHtml =
                        '<div class="tm-note-card">' +
                            '<div class="d-flex justify-content-between align-items-center mb-2">' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<span class="tm-note-avatar">' + escapeText(initial) + '</span>' +
                                    '<div>' +
                                        '<div class="fw-bold text-white" style="font-size:13px;">' + escapeText(item.author) + '</div>' +
                                        '<div class="text-secondary" style="font-size:11px;">' + escapeText(item.time) + '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<button type="button" class="btn btn-sm btn-link text-secondary p-0 tm-delete-note" data-id="' + escapeText(item.id) + '" title="Hapus">' +
                                    '<i class="bi bi-trash3"></i>' +
                                '</button>' +
                            '</div>' +
                            '<div class="tm-note-body text-light">' + item.html + '</div>' +
                        '</div>';
                    $list.append(cardHtml);
                });
            }

            function fetchNotes() {
                $.get(indexUrl)
                    .done(function (res) {
                        renderNotesList(res.notes || []);
                    })
                    .fail(function () {
                        $('#tm-note-feedback').text('Gagal memuat catatan dari database.');
                    });
            }

            $('#tm-anon-name').on('input change', function () {
                const val = $(this).val().trim();
                if (val) {
                    localStorage.setItem(storageKeyName, val);
                }
            });

            $('#tm-random-name').on('click', function () {
                const freshName = pickRandomName();
                $('#tm-anon-name').val(freshName).trigger('change');
                $('#tm-note-feedback').text('Nama diganti menjadi: ' + freshName);
            });

            $('#tm-clear-editor').on('click', function () {
                quill.setText('');
                $('#tm-note-feedback').text('');
            });

            $('#tm-post-note').on('click', function () {
                const plainText = quill.getText().trim();
                if (!plainText) {
                    $('#tm-note-feedback').text('Isi catatan dulu sebelum dikirim.');
                    return;
                }

                let author = $('#tm-anon-name').val().trim();
                if (!author) {
                    author = pickRandomName();
                    $('#tm-anon-name').val(author);
                }
                localStorage.setItem(storageKeyName, author);

                const $btn = $('#tm-post-note').prop('disabled', true);

                $.ajax({
                    url: storeUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        author: author,
                        content_html: quill.root.innerHTML
                    }
                })
                    .done(function () {
                        quill.setText('');
                        $('#tm-note-feedback').text('Catatan tersimpan ke database.');
                        fetchNotes();
                    })
                    .fail(function () {
                        $('#tm-note-feedback').text('Gagal menyimpan catatan.');
                    })
                    .always(function () {
                        $btn.prop('disabled', false);
                    });
            });

            $('#tm-notes-list').on('click', '.tm-delete-note', function () {
                const targetId = $(this).data('id');
                if (!targetId) return;

                $.ajax({
                    url: destroyBaseUrl + '/' + targetId,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        _method: 'DELETE'
                    }
                })
                    .done(function () {
                        $('#tm-note-feedback').text('Catatan dihapus.');
                        fetchNotes();
                    })
                    .fail(function () {
                        $('#tm-note-feedback').text('Gagal menghapus catatan.');
                    });
            });

            fetchNotes();
        });
    </script>
@endpush