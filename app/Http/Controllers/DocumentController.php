<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\TextChunker;
use Illuminate\Http\Request;
use App\Jobs\GenerateChunkEmbedding;

class DocumentController extends Controller
{
    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request, TextChunker $chunker)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $document = Document::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'],
            'source_type' => 'text',
            'content_hash' => hash('sha256', $data['content']),
            'user_id' => $request->user()->id,
        ]);

        foreach ($chunker->chunk($document->content) as $position => $text) {
            $chunk = $document->chunks()->create([
                'position' => $position,
                'content' => $text,
            ]);

            GenerateChunkEmbedding::dispatch($chunk);
        }

        return redirect()
            -> route('documents.create')
            -> with('status', "Documento guardado com {$document->chunks()->count()} pedaços.");
    }
}
