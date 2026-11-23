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

</div>
