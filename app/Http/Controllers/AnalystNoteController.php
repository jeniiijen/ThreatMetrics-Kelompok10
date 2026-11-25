<?php

namespace App\Http\Controllers;

use App\Models\AnalystNote;
use Illuminate\Http\Request;

class AnalystNoteController extends Controller
{
    public function index()
    {
        $notes = AnalystNote::query()
            ->orderByDesc('id')
            ->take(50)
            ->get()
            ->map(function (AnalystNote $note) {
                return [
                    'id' => $note->id,
                    'author' => (string) $note->author,
                    'time' => $note->created_at
                        ? $note->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i')
                        : now('Asia/Jakarta')->format('d M Y, H:i'),
                    'html' => (string) $note->content_html,
                ];
            });

        return response()->json(['notes' => $notes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'author' => ['required', 'string', 'max:60'],
            'content_html' => ['required', 'string'],
        ]);

        $note = AnalystNote::create($validated);

        return response()->json([
            'note' => [
                'id' => $note->id,
                'author' => (string) $note->author,
                'time' => $note->created_at
                    ? $note->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i')
                    : now('Asia/Jakarta')->format('d M Y, H:i'),
                'html' => (string) $note->content_html,
            ],
        ], 201);
    }

    public function destroy(AnalystNote $analystNote)
    {
        $analystNote->delete();

        return response()->json(['ok' => true]);
    }
}