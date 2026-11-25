<div class="tm-ioc-section" id="analyst-notes-section">
    <div class="tm-ioc-card">
        <div class="tm-panel-header">
            <div>
                <h5 class="tm-panel-title">
                    <i class="bi bi-chat-square-text me-2" style="color:var(--tm-cyan);"></i>
                    Analyst Notes &amp; Quick Discussion
                </h5>
                <div class="tm-panel-subtitle">
                    Catatan tim anonim dengan nama panggilan acak yang muncul di setiap postingan
                </div>
            </div>
            <span class="tm-chart-tag">ANONYMOUS NOTES</span>
        </div>

        <div class="tm-panel-body tm-notes-wrap">
            <div id="analystNoteNotice" class="tm-refresh-notice" style="display:none;"></div>

            <form id="analystNoteForm" action="{{ route('analyst-notes.store') }}" method="POST" class="mb-4">
                @csrf

                <div class="mb-3">
                    <label for="analystAliasInput" class="form-label" style="font-size:11px;color:#8295ad;">
                        Nama panggilan anonim
                    </label>
                    <div class="input-group tm-alias-group">
                        <span class="input-group-text tm-alias-addon">
                            <i class="bi bi-incognito"></i>
                        </span>
                        <input type="text"
                               id="analystAliasInput"
                               name="alias"
                               class="form-control tm-dark-input"
                               value="Penjaga Log Malam"
                               placeholder="Nama panggilan anonim...">
                        <button type="button"
                                id="randomizeAliasBtn"
                                class="btn tm-alias-shuffle-btn"
                                title="Acak nama panggilan">
                            <i class="bi bi-shuffle"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3 tm-quill-dark-box">
                    <div id="analystQuillToolbar" class="ql-toolbar ql-snow">
                        <span class="ql-formats">
                            <button type="button" class="ql-bold"></button>
                            <button type="button" class="ql-italic"></button>
                            <button type="button" class="ql-underline"></button>
                            <button type="button" class="ql-strike"></button>
                        </span>
                        <span class="ql-formats">
                            <button type="button" class="ql-list" value="ordered"></button>
                            <button type="button" class="ql-list" value="bullet"></button>
                        </span>
                        <span class="ql-formats">
                            <button type="button" class="ql-blockquote"></button>
                            <button type="button" class="ql-code-block"></button>
                        </span>
                        <span class="ql-formats">
                            <button type="button" class="ql-link"></button>
                            <button type="button" class="ql-clean"></button>
                        </span>
                    </div>
                    <div id="analystQuillEditor" class="ql-container ql-snow"></div>
                    <input type="hidden" name="content" id="analystNoteContentInput">
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" id="submitAnalystNoteBtn" class="btn btn-primary px-4">
                        <i class="bi bi-send me-1"></i> Kirim Catatan
                    </button>
                </div>
            </form>

            <div id="analystNotesList" class="d-flex flex-column gap-3">
                <div class="tm-empty" style="min-height:90px;">Memuat catatan analis...</div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<style>
    .tm-notes-wrap,
    .tm-notes-wrap .form-control,
    .tm-notes-wrap .input-group-text,
    .tm-notes-wrap .ql-toolbar,
    .tm-notes-wrap .ql-toolbar.ql-snow,
    .tm-notes-wrap .ql-container,
    .tm-notes-wrap .ql-container.ql-snow,
    .tm-notes-wrap .ql-editor,
    .tm-notes-wrap .ql-editor.ql-blank::before {
        background-color: #0c1625 !important;
        color: #f2f6fc !important;
        border-color: #26364b !important;
    }

    .tm-alias-addon {
        background-color: #152235 !important;
        color: #13c9dc !important;
        border-color: #26364b !important;
    }

    .tm-alias-shuffle-btn {
        background-color: #152235 !important;
        color: #a4b5ca !important;
        border: 1px solid #26364b !important;
    }

    .tm-alias-shuffle-btn:hover {
        color: #13c9dc !important;
        border-color: #13c9dc !important;
    }

    .tm-quill-dark-box {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #26364b !important;
        background-color: #0c1625 !important;
    }

    .tm-notes-wrap .ql-toolbar.ql-snow {
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: 1px solid #26364b !important;
        background-color: #152235 !important;
    }

    .tm-notes-wrap .ql-container.ql-snow {
        border: none !important;
        min-height: 120px;
        font-size: 13px;
    }

    .tm-notes-wrap .ql-editor {
        min-height: 120px;
        color: #f2f6fc !important;
        background-color: #0c1625 !important;
    }

    .tm-notes-wrap .ql-snow .ql-stroke {
        stroke: #9fb1c7 !important;
    }

    .tm-notes-wrap .ql-snow .ql-fill {
        fill: #9fb1c7 !important;
    }

    .tm-notes-wrap .ql-snow .ql-picker-label {
        color: #9fb1c7 !important;
    }

    .tm-notes-wrap .ql-snow button:hover .ql-stroke,
    .tm-notes-wrap .ql-snow button.ql-active .ql-stroke {
        stroke: #13c9dc !important;
    }

    .tm-notes-wrap .ql-snow button:hover .ql-fill,
    .tm-notes-wrap .ql-snow button.ql-active .ql-fill {
        fill: #13c9dc !important;
    }

    .tm-note-card {
        background-color: #0c1625 !important;
        border: 1px solid #26364b !important;
        border-radius: 11px;
        padding: 14px 16px;
    }

    .tm-note-alias-badge {
        background-color: #102e40 !important;
        color: #13c9dc !important;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .tm-note-body {
        color: #e8f1fc !important;
        font-size: 13px;
        margin-top: 8px;
        word-break: break-word;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const aliasInput = document.getElementById('analystAliasInput');
    const shuffleBtn = document.getElementById('randomizeAliasBtn');
    const noteForm = document.getElementById('analystNoteForm');
    const contentInput = document.getElementById('analystNoteContentInput');
    const notesListEl = document.getElementById('analystNotesList');
    const noticeEl = document.getElementById('analystNoteNotice');

    const aliases = [
        'Penjaga Log Malam',
        'Pemburu IOC Sunyi',
        'Pengamat Paket',
        'Lentera SOC',
        'Penelusur Hash',
        'Sentinel Malam'
    ];

    if (shuffleBtn && aliasInput) {
        shuffleBtn.addEventListener('click', function () {
            const current = aliasInput.value.trim();
            const candidates = aliases.filter((name) => name !== current);
            const picked = candidates[Math.floor(Math.random() * candidates.length)] || aliases[0];
            aliasInput.value = picked;
        });
    }

    let quill = null;
    if (window.Quill && document.getElementById('analystQuillEditor')) {
        quill = new Quill('#analystQuillEditor', {
            theme: 'snow',
            placeholder: 'Tulis catatan analisis atau diskusi cepat...',
            modules: {
                toolbar: '#analystQuillToolbar'
            }
        });
    }

    function showNoteNotice(type, text) {
        if (!noticeEl) return;
        noticeEl.className = 'tm-refresh-notice ' + type;
        noticeEl.style.display = 'block';
        noticeEl.textContent = text;
    }

    function escapeText(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    async function loadAnalystNotes() {
        if (!notesListEl) return;

        try {
            const response = await fetch(@json(route('analyst-notes.index')), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const payload = await response.json();
            const items = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : []);

            if (items.length === 0) {
                notesListEl.innerHTML = '<div class="tm-empty" style="min-height:80px;">Belum ada catatan analis.</div>';
                return;
            }

            notesListEl.innerHTML = items.map(function (item) {
                const alias = item.alias || item.author || item.nickname || 'Penjaga Log Malam';
                const bodyHtml = item.content || item.body || item.note || '';
                const created = item.created_at_formatted || item.created_at || '';

                return (
                    '<div class="tm-note-card">' +
                        '<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">' +
                            '<span class="tm-note-alias-badge"><i class="bi bi-incognito me-1"></i>' + escapeText(alias) + '</span>' +
                            '<span style="font-size:11px;color:#8295ad;">' + escapeText(created) + '</span>' +
                        '</div>' +
                        '<div class="tm-note-body">' + bodyHtml + '</div>' +
                    '</div>'
                );
            }).join('');
        } catch (err) {
            notesListEl.innerHTML = '<div class="tm-empty" style="min-height:80px;">Catatan analis belum dapat dimuat (' + escapeText(err.message) + ').</div>';
        }
    }

    loadAnalystNotes();

    if (noteForm) {
        noteForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const htmlContent = quill ? quill.root.innerHTML.trim() : '';
            const plainText = quill ? quill.getText().trim() : '';

            if (!plainText) {
                showNoteNotice('error', 'Isi catatan tidak boleh kosong.');
                return;
            }

            if (contentInput) {
                contentInput.value = htmlContent;
            }

            try {
                const formData = new FormData(noteForm);
                formData.set('content', htmlContent);

                const response = await fetch(noteForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                if (!response.ok || result.success === false) {
                    throw new Error(result.message || 'Gagal menyimpan catatan.');
                }

                if (quill) {
                    quill.setContents([]);
                }
                showNoteNotice('success', result.message || 'Catatan anonim tersimpan.');
                loadAnalystNotes();
            } catch (err) {
                showNoteNotice('error', err.message);
            }
        });
    }
});
</script>
@endpush