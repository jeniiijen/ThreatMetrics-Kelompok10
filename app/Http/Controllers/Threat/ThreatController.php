<?php

namespace App\Http\Controllers\Threat;

use App\Http\Controllers\Controller;
use App\Models\Threat;
use Illuminate\Http\Request;

class ThreatController extends Controller
{
    // Redirect index requests to dashboard while keeping query parameters
    public function index(Request $request)
    {
        return redirect()->route('dashboard', $request->query());
    }

    public function create()
    {
        return view('threats.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'user' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:2048',
            'tags' => 'nullable|string',
            'tweet' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Normalize indicator type and tags before saving
        $validated['type'] = strtoupper(trim($validated['type']));
        $validated['tags'] = $this->formatTags($validated['tags'] ?? '');
        $validated['notes'] = $request->input('notes') ?: null;

        Threat::create($validated);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Data ancaman berhasil ditambahkan.');
    }

    public function show($threatmetric)
    {
        $threat = $this->resolveThreatRecord($threatmetric);

        return view('threats.show', compact('threat'));
    }

    public function edit($threatmetric)
    {
        $threat = $this->resolveThreatRecord($threatmetric);

        return view('threats.edit', compact('threat'));
    }

    public function update(Request $request, $threatmetric)
    {
        $threat = $this->resolveThreatRecord($threatmetric);

        $validated = $request->validate([
            'date' => 'required|date',
            'user' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:2048',
            'tags' => 'nullable|string',
            'tweet' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Normalize indicator type and tags before updating
        $validated['type'] = strtoupper(trim($validated['type']));
        $validated['tags'] = $this->formatTags($validated['tags'] ?? '');
        $validated['notes'] = $request->input('notes') ?: null;

        $threat->update($validated);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Data ancaman berhasil diperbarui.');
    }

    public function destroy($threatmetric)
    {
        $threat = $this->resolveThreatRecord($threatmetric);
        $threat->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Data ancaman berhasil dihapus.');
    }

    // Load threat model reliably whether passed as ID, threat_id code, or model instance
    private function resolveThreatRecord($threatmetric): Threat
    {
        if ($threatmetric instanceof Threat && $threatmetric->exists) {
            return $threatmetric;
        }

        $key = $threatmetric instanceof Threat
            ? ($threatmetric->getKey() ?? $threatmetric->threat_id)
            : $threatmetric;

        if (is_numeric($key)) {
            return Threat::query()->findOrFail((int) $key);
        }

        return Threat::query()->where('threat_id', (string) $key)->firstOrFail();
    }

    // Convert comma-separated or array tags into a clean array
    private function formatTags($tags): array
    {
        if (is_array($tags)) {
            return array_values(array_filter(array_map('trim', $tags)));
        }

        return collect(explode(',', (string) $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
    }
}