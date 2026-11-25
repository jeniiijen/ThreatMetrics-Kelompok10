<?php

namespace App\Http\Controllers;

use App\Models\AnalystNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AnalystNoteController extends Controller
{
    public function index(): JsonResponse
    {
        if (! Schema::hasTable('analyst_notes')) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $notes = AnalystNote::query()
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(function (AnalystNote $note) {
                $author = $note->author
                    ?? $note->alias
                    ?? $note->nickname
                    ?? 'Penjaga Log Malam';

                $contentHtml = $note->content_html
                    ?? $note->content
                    ?? $note->body
                    ?? '';

                $createdAt = '';
                if (! empty($note->created_at)) {
                    try {
                        $createdAt = Carbon::parse($note->created_at)
                            ->timezone('Asia/Jakarta')
                            ->format('d M Y, H:i');
                    } catch (\Throwable $e) {
                        $createdAt = (string) $note->created_at;
                    }
                }

                return [
                    'id' => $note->id,
                    'author' => (string) $author,
                    'content_html' => (string) $contentHtml,
                    'created_at_formatted' => $createdAt,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'author' => ['nullable', 'string', 'max:100'],
            'alias' => ['nullable', 'string', 'max:100'],
            'content_html' => ['nullable', 'string', 'max:20000'],
            'content' => ['nullable', 'string', 'max:20000'],
        ]);

        if (! Schema::hasTable('analyst_notes')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabel analyst_notes belum tersedia.',
            ], 500);
        }

        $rawAuthor = $validated['author'] ?? $validated['alias'] ?? '';
        $author = trim((string) $rawAuthor);
        if ($author === '') {
            $author = 'Penjaga Log Malam';
        }

        $contentHtml = trim((string) ($validated['content_html'] ?? $validated['content'] ?? ''));
        if ($contentHtml === '') {
            return response()->json([
                'success' => false,
                'message' => 'Isi catatan tidak boleh kosong.',
            ], 422);
        }

        $note = AnalystNote::create([
            'author' => $author,
            'content_html' => $contentHtml,
        ]);

        $createdFormatted = $note->created_at
            ? Carbon::parse($note->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i')
            : now()->timezone('Asia/Jakarta')->format('d M Y, H:i');

        return response()->json([
            'success' => true,
            'message' => 'Catatan anonim berhasil disimpan.',
            'data' => [
                'id' => $note->id,
                'author' => $note->author,
                'content_html' => $note->content_html,
                'created_at_formatted' => $createdFormatted,
            ],
        ], 201);
    }

    public function destroy(AnalystNote $analystNote): JsonResponse
    {
        $analystNote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan dihapus.',
        ]);
    }
}