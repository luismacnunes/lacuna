<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\TextChunker;
use Illuminate\Http\Request;
use App\Jobs\GenerateChunkEmbedding;
use App\Services\TopicResolver;
use App\Jobs\GenerateIngestionQuestions;

class DocumentController extends Controller
{
    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request, TextChunker $chunker, TopicResolver $topics)
    {
        $data = $request->validate([
            'topic' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $topic = $topics->resolve($data['topic']);

        $document = Document::create([
            'topic_id' => $topic->id,
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'],
            'source_type' => 'text',
            'content_hash' => hash('sha256', $data['content']),
        ]);

        $topic->update(['has_material' => true]);

        if (! $topic->owner_id) {
            $topic->update(['owner_id' => $request->user()->id]);
        }

        foreach ($chunker->chunk($document->content) as $position => $text) {
            $chunk = $document->chunks()->create([
                'position' => $position,
                'content' => $text,
            ]);

            GenerateChunkEmbedding::dispatch($chunk);
        }

        GenerateIngestionQuestions::dispatch($document);

        return redirect()
            ->route('documents.create')
            ->with('status', 'Documento guardado. As perguntas de contexto vão aparecer na fila.');
    }
}
