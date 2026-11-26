<?php

namespace App\Http\Controllers;

use App\Models\Threat;
use Illuminate\Http\Request;

class ThreatController extends Controller
{
    /**
     * Menampilkan daftar seluruh data ancaman.
     */
    public function index(Request $request)
    {
        $query = Threat::query();

        // Pencarian data
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('threat_id', 'like', "%{$search}%")
                    ->orWhere('user', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('tweet', 'like', "%{$search}%");
            });
        }

        // Filter jenis ancaman
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter tanggal
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $threats = $query
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        // Statistik jumlah data
        $totalThreats = Threat::count();

        $typeCounts = Threat::selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('threats.index', compact(
            'threats',
            'totalThreats',
            'typeCounts'
        ));
    }

    /**
     * Menampilkan form tambah data.
     */
    public function create()
    {
        return view('threats.create');
    }

    /**
     * Menyimpan data baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'user' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'value' => 'required|numeric',
            'tags' => 'nullable|string',
            'tweet' => 'nullable|string',
        ]);

        $validated['tags'] = $this->formatTags(
            $validated['tags'] ?? ''
        );

        Threat::create($validated);

        return redirect()
            ->route('threatmetrics.index')
            ->with('success', 'Data ancaman berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail data.
     */
    public function show(Threat $threatmetric)
    {
        $threat = $threatmetric;

        return view('threats.show', compact('threat'));
    }

    /**
     * Menampilkan form edit.
     */
    public function edit(Threat $threatmetric)
    {
        $threat = $threatmetric;

        return view('threats.edit', compact('threat'));
    }

    /**
     * Memperbarui data.
     */
    public function update(Request $request, Threat $threatmetric)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'user' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'value' => 'required|numeric',
            'tags' => 'nullable|string',
            'tweet' => 'nullable|string',
        ]);

        $validated['tags'] = $this->formatTags(
            $validated['tags'] ?? ''
        );

        $threatmetric->update($validated);

        return redirect()
            ->route('threatmetrics.index')
            ->with('success', 'Data ancaman berhasil diperbarui.');
    }

    /**
     * Menghapus data secara permanen.
     */
    public function destroy($threatmetric)
    {
        $deleted = Threat::where('id', $threatmetric)->delete();

        if ($deleted === 0) {
            return redirect()
                ->route('threatmetrics.index')
                ->with('error', 'Data tidak ditemukan atau sudah dihapus.');
        }

        return redirect()
            ->route('threatmetrics.index')
            ->with('success', 'Data ancaman berhasil dihapus.');
    }

    /**
     * Mengubah tags dari teks menjadi array.
     */
    private function formatTags($tags)
    {
        if (is_array($tags)) {
            return array_values(array_filter($tags));
        }

        return collect(explode(',', $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
    }
}
