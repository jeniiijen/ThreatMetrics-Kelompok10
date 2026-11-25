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
                    <label for="analystAliasInput" class="form-label" style="font-size:11px;color:#8fa3bc;">
                        Nama panggilan anonim
                    </label>
                    <div class="input-group tm-alias-group">
                        <span class="input-group-text tm-alias-addon">
                            <i class="bi bi-incognito"></i>
                        </span>
                        <input type="text"
                               id="analystAliasInput"
                               name="author"
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
                    <input type="hidden" name="content_html" id="analystNoteContentInput">
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" id="submitAnalystNoteBtn" class="btn btn-primary px-4">
                        <i class="bi bi-send me-1"></i> Kirim Catatan
                    </button>
                </div>
            </form>

            <div class="tm-news-comments-head">
                <span class="tm-news-comments-title">Komentar (<span id="analystNotesCountBadge">0</span>)</span>
                <span class="tm-news-comments-sub">Urutan terbaru</span>
            </div>

            <div id="analystNotesList" class="tm-news-comment-list">
                <div class="tm-empty" style="min-height:80px;">Memuat catatan analis...</div>
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

    /* Gaya thread komentar berita */
    .tm-news-comments-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 0 10px;
        border-top: 1px solid #223349;
        border-bottom: 1px solid #223349;
    }

    .tm-news-comments-title {
        font-size: 14px;
        font-weight: 700;
        color: #f2f6fc;
    }

    .tm-news-comments-sub {
        font-size: 11px;
        color: #8295ad;
    }

    .tm-news-comment-list {
        display: flex;
        flex-direction: column;
    }

    .tm-news-comment-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 4px;
        border-bottom: 1px solid rgba(130, 149, 173, 0.14);
    }

    .tm-news-comment-row:last-child {
        border-bottom: none;
    }

    .tm-news-avatar {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        background-color: #14283e;
        border: 1px solid #2c4764;
        color: #19d8ec;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        margin-top: 2px;
    }

    .tm-news-comment-content-wrap {
        flex: 1;
        min-width: 0;
    }

    .tm-news-comment-header {
        display: flex;
        align-items: baseline;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 4px;
    }

    .tm-news-author {
        font-size: 13px;
        font-weight: 700;
        color: #f2f6fc;
    }

    .tm-news-time {
        font-size: 11px;
        color: #8295ad;
    }

    .tm-news-body {
        font-size: 13.5px;
        line-height: 1.6;
        color: #dce7f5;
        word-break: break-word;
    }

    .tm-news-body p {
        margin-bottom: 6px;
    }

    .tm-news-body p:last-child {
        margin-bottom: 0;
    }

    .tm-news-body a {
        color: #29dcff;
        text-decoration: underline;
    }

    .tm-news-body blockquote {
        margin: 6px 0;
        padding: 4px 10px;
        border-left: 2px solid #13c9dc;
        color: #b7c9df;
    }

    .tm-news-body pre,
    .tm-news-body code {
        background-color: #08101c;
        color: #7ee787;
        border: 1px solid #22344b;
        border-radius: 5px;
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
    }

    .tm-news-body pre {
        padding: 8px 10px;
        overflow-x: auto;
    }

    .tm-news-body code {
        padding: 1px 4px;
    }

    .tm-news-body ul,
    .tm-news-body ol {
        padding-left: 18px;
        margin-bottom: 6px;
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
    const countBadgeEl = document.getElementById('analystNotesCountBadge');
    const noticeEl = document.getElementById('analystNoteNotice');
    const notesIndexUrl = @json(route('analyst-notes.index'));

    const aliases = [
        'Penjaga Log Malam',
        'Pemburu IOC Sunyi',
        'Pengamat Paket',
        'Lentera SOC',
        'Penelusur Hash',
        'Sentinel Malam',
        'Kapten Phishing Radar'
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

    function getInitials(name) {
        const parts = String(name || 'A').trim().split(/\s+/).filter(Boolean);
        if (parts.length === 0) return 'A';
        if (parts.length === 1) return parts[0].slice(0, 1).toUpperCase();
        return (parts[0].slice(0, 1) + parts[1].slice(0, 1)).toUpperCase();
    }

    function parseJsonSafely(rawText) {
        const cleaned = String(rawText || '').replace(/^\uFEFF/, '').trim();
        if (!cleaned) {
            return { success: true, data: [] };
        }
        return JSON.parse(cleaned);
    }

    function updateCountBadge(total) {
        if (!countBadgeEl) return;
        countBadgeEl.textContent = String(total);
    }

    async function loadAnalystNotes() {
        if (!notesListEl) return;

        try {
            const response = await fetch(notesIndexUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const rawText = await response.text();
            let payload;
            try {
                payload = parseJsonSafely(rawText);
            } catch (jsonErr) {
                const snippet = rawText.replace(/\s+/g, ' ').trim().slice(0, 140);
                throw new Error('HTTP ' + response.status + ' bukan JSON valid: ' + (snippet || jsonErr.message));
            }

            if (!response.ok) {
                throw new Error(payload.message || ('HTTP ' + response.status));
            }

            const items = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : []);
            updateCountBadge(items.length);

            if (items.length === 0) {
                notesListEl.innerHTML = '<div class="tm-empty" style="min-height:80px;">Belum ada komentar analis.</div>';
                return;
            }

            notesListEl.innerHTML = items.map(function (item) {
                const authorName = item.author || item.alias || item.nickname || 'Penjaga Log Malam';
                const bodyHtml = item.content_html || item.content || item.body || item.note || '';
                const created = item.created_at_formatted || item.created_at || '';
                const initials = getInitials(authorName);

                return (
                    '<div class="tm-news-comment-row">' +
                        '<div class="tm-news-avatar" aria-hidden="true">' + escapeText(initials) + '</div>' +
                        '<div class="tm-news-comment-content-wrap">' +
                            '<div class="tm-news-comment-header">' +
                                '<span class="tm-news-author">' + escapeText(authorName) + '</span>' +
                                '<span class="tm-news-time">&bull; ' + escapeText(created) + '</span>' +
                            '</div>' +
                            '<div class="tm-news-body">' + bodyHtml + '</div>' +
                        '</div>' +
                    '</div>'
                );
            }).join('');
        } catch (err) {
            updateCountBadge(0);
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
                formData.set('content_html', htmlContent);

                const response = await fetch(noteForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const rawText = await response.text();
                let result;
                try {
                    result = parseJsonSafely(rawText);
                } catch (jsonErr) {
                    const snippet = rawText.replace(/\s+/g, ' ').trim().slice(0, 140);
                    throw new Error('HTTP ' + response.status + ' bukan JSON valid: ' + (snippet || jsonErr.message));
                }

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