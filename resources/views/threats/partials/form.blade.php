@php
    $record = $threat ?? null;
    $dateValue = old(
        'date',
        ($record && $record->date)
            ? \Carbon\Carbon::parse($record->date)->format('Y-m-d\TH:i')
            : now('Asia/Jakarta')->format('Y-m-d\TH:i')
    );

    $rawTags = old('tags', $record->tags ?? '');
    if (is_array($rawTags)) {
        $tagsString = implode(', ', array_map(fn ($t) => trim(str_replace('"', '', (string) $t)), $rawTags));
    } elseif (is_string($rawTags) && str_starts_with(trim($rawTags), '[')) {
        $decoded = json_decode($rawTags, true);
        $tagsString = is_array($decoded)
            ? implode(', ', array_map(fn ($t) => trim(str_replace('"', '', (string) $t)), $decoded))
            : $rawTags;
    } else {
        $tagsString = (string) $rawTags;
    }

    $currentType = strtoupper((string) old('type', $record->type ?? ''));
    $allowedTypes = ['MD5', 'DOMAIN', 'URL', 'IP', 'SHA256'];
@endphp

<div class="row g-3">
    {{-- DATE --}}
    <div class="col-md-6">
        <label for="date" class="form-label">Date Time</label>
        <input type="datetime-local"
               name="date"
               id="date"
               class="form-control"
               value="{{ $dateValue }}"
               required>
        @if(isset($errors) && $errors->has('date'))
            <small class="text-danger">{{ $errors->first('date') }}</small>
        @endif
    </div>

    {{-- USER --}}
    <div class="col-md-6">
        <label for="user" class="form-label">Reporting User / Source</label>
        <input type="text"
               name="user"
               id="user"
               class="form-control"
               value="{{ old('user', $record->user ?? '') }}"
               placeholder="e.g., C2Tracker, AbuseIPDB, Analyst"
               required>
        @if(isset($errors) && $errors->has('user'))
            <small class="text-danger">{{ $errors->first('user') }}</small>
        @endif
    </div>

    {{-- TYPE --}}
    <div class="col-md-4">
        <label for="type" class="form-label">IOC Type</label>
        <select name="type" id="type" class="form-select" required>
            <option value="">-- Select IOC Type --</option>
            @foreach($allowedTypes as $iocType)
                <option value="{{ $iocType }}" {{ $currentType === $iocType ? 'selected' : '' }}>
                    {{ $iocType }}
                </option>
            @endforeach
        </select>
        @if(isset($errors) && $errors->has('type'))
            <small class="text-danger">{{ $errors->first('type') }}</small>
        @endif
    </div>

    {{-- VALUE --}}
    <div class="col-md-8">
        <label for="value" class="form-label">Indicator / Value</label>
        <input type="text"
               name="value"
               id="value"
               class="form-control"
               value="{{ old('value', $record->value ?? '') }}"
               placeholder="e.g., 103.245.192.1, evil-domain.com, https://..., hash"
               required>
        @if(isset($errors) && $errors->has('value'))
            <small class="text-danger">{{ $errors->first('value') }}</small>
        @endif
    </div>

    {{-- TAGS --}}
    <div class="col-md-6">
        <label for="tags" class="form-label">Tags (comma separated)</label>
        <input type="text"
               name="tags"
               id="tags"
               class="form-control"
               value="{{ $tagsString }}"
               placeholder="e.g., phishing, malware, botnet">
        @if(isset($errors) && $errors->has('tags'))
            <small class="text-danger">{{ $errors->first('tags') }}</small>
        @endif
    </div>

    {{-- TWEET / CONTEXT --}}
    <div class="col-md-6">
        <label for="tweet" class="form-label">Reference / Context (Optional)</label>
        <input type="text"
               name="tweet"
               id="tweet"
               class="form-control"
               value="{{ old('tweet', $record->tweet ?? '') }}"
               placeholder="Optional feed note or reference URL">
        @if(isset($errors) && $errors->has('tweet'))
            <small class="text-danger">{{ $errors->first('tweet') }}</small>
        @endif
    </div>
</div>