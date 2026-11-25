<div class="row g-3">

    {{-- DATE TIME --}}
    <div class="col-md-6">
        <label for="date" class="form-label">Date Time</label>
        <input type="datetime-local"
               name="date"
               id="date"
               class="form-control"
               value="{{ old('date', isset($threat) && $threat->date ? \Carbon\Carbon::parse($threat->date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
               required>
        @error('date')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- USER --}}
    <div class="col-md-6">
        <label for="user" class="form-label">User</label>
        <input type="text"
               name="user"
               id="user"
               class="form-control"
               value="{{ old('user', $threat->user ?? '') }}"
               placeholder="Enter username"
               required>
        @error('user')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- TYPE --}}
    <div class="col-md-6">
        <label for="type" class="form-label">Type</label>
        <select name="type"
                id="type"
                class="form-select"
                required>
            <option value="">Select Type</option>
            @foreach(['MD5', 'DOMAIN', 'URL', 'IP'] as $type)
                <option value="{{ $type }}"
                    {{ old('type', $threat->type ?? '') === $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
        @error('type')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- INDICATOR / VALUE --}}
    <div class="col-md-6">
        <label for="value" class="form-label">Indicator / Value</label>
        <input type="text"
               name="value"
               id="value"
               class="form-control"
               value="{{ old('value', $threat->value ?? '') }}"
               placeholder="Enter indicator value"
               required>
        @error('value')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- TAGS --}}
    <div class="col-12">
        <label for="tags" class="form-label">Tags</label>
        <input type="text"
               name="tags"
               id="tags"
               class="form-control"
               value="{{ old('tags', isset($threat) ? (is_array($threat->tags) ? implode(', ', $threat->tags) : $threat->tags) : '') }}"
               placeholder="Example: adware, malware, phishing">

        <small class="text-secondary">
            Separate multiple tags using commas.
        </small>

        @error('tags')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- TWEET / DESCRIPTION --}}
    <div class="col-12">
        <label for="tweet" class="form-label">Tweet / Description</label>
        <textarea name="tweet"
                  id="tweet"
                  rows="4"
                  class="form-control"
                  placeholder="Enter threat description...">{{ old('tweet', $threat->tweet ?? '') }}</textarea>

        @error('tweet')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    {{-- NOTES / ANALYSIS (Quill Editor + jQuery) - Anggota 5 --}}
    <div class="col-12">
        <label class="form-label">
            <i class="bi bi-journal-text me-1"></i> Catatan / Analisis Threat
        </label>

        {{-- Konten Quill disalin ke input tersembunyi ini saat submit --}}
        <input type="hidden" name="notes" id="notes" value="{{ old('notes', $threat->notes ?? '') }}">

        {{-- Wadah editor Quill --}}
        <div id="quill-editor"
             class="form-control"
             style="padding:0; height:auto; min-height:180px;">
            {!! old('notes', $threat->notes ?? '') !!}
        </div>

        <small class="text-secondary">
            Gunakan toolbar untuk memformat catatan/analisis (tebal, miring, daftar, dll.).
        </small>

        @error('notes')
            <small class="text-danger d-block">{{ $message }}</small>
        @enderror
    </div>

</div>

@once
    @push('styles')
        {{-- Quill Editor CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
        <style>
            /* Tema gelap untuk Quill agar cocok dengan aplikasi */
            #quill-editor {
                background-color: #263543;
                border: 1px solid #344453;
                border-radius: 7px;
            }
            #quill-editor .ql-toolbar.ql-snow {
                background-color: #1e2b37;
                border: none;
                border-bottom: 1px solid #344453;
                border-radius: 7px 7px 0 0;
            }
            #quill-editor .ql-toolbar.ql-snow .ql-picker-label,
            #quill-editor .ql-toolbar.ql-snow .ql-stroke,
            #quill-editor .ql-toolbar.ql-snow .ql-picker {
                color: #91a4b7;
                stroke: #91a4b7;
            }
            #quill-editor .ql-container.ql-snow {
                border: none;
                color: #f0f4f8;
                font-size: 14px;
                min-height: 160px;
            }
            #quill-editor .ql-editor {
                min-height: 160px;
            }
            #quill-editor .ql-editor.ql-blank::before {
                color: #8da4bb;
                font-style: normal;
            }
        </style>
    @endpush

    @push('scripts')
        {{-- jQuery (dibutuhkan Quill + interaksi form) --}}
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        {{-- Quill Editor JS --}}
        <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

        <script>
            (function ($) {
                // Inisialisasi Quill hanya jika elemen editor tersedia.
                const editorEl = document.getElementById('quill-editor');
                if (!editorEl || window.__quillEditorInit) {
                    return;
                }
                window.__quillEditorInit = true;

                const quill = new Quill(editorEl, {
                    theme: 'snow',
                    placeholder: 'Tulis catatan atau analisis ancaman di sini...',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'align': [] }],
                            ['link', 'blockquote', 'code-block'],
                            ['clean']
                        ]
                    }
                });

                // Saat form disubmit, isi input tersembunyi #notes dengan HTML Quill.
                const form = editorEl.closest('form');
                const notesInput = document.getElementById('notes');

                if (form) {
                    form.addEventListener('submit', function () {
                        notesInput.value = quill.root.innerHTML.trim();
                    });
                }
            })(window.jQuery || window);
        </script>
    @endpush
@endonce
