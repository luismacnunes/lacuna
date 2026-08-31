<?php

namespace App\Http\Controllers;

use App\Enums\PendingStatus;
use App\Jobs\GenerateCuratedAnswerEmbedding;
use App\Models\CuratedAnswer;
use App\Models\PendingQuestion;
use Illuminate\Http\Request;

class CurationController extends Controller
{
    public function edit(PendingQuestion $pending)
    {
        $pending->load(['topic', 'document']);

        return view('curation.edit', ['pending' => $pending]);
    }

    public function update(Request $request, PendingQuestion $pending)
    {
        $data = $request->validate([
            'answer' => 'required|string|min:10',
            'topic_name' => 'required|string|max:255',
        ]);

        $pending->topic->update(['name' => $data['topic_name']]);

        $curated = CuratedAnswer::create([
            'topic_id' => $pending->topic_id,
            'author_id' => $request->user()->id,
            'question' => $pending->text,
            'answer' => $data['answer'],
        ]);

        $pending->update([
            'status' => PendingStatus::Answered,
            'answered_by' => $request->user()->id,
            'curated_answer_id' => $curated->id,
        ]);

        GenerateCuratedAnswerEmbedding::dispatch($curated);

        return redirect()
            ->route('queue.index')
            ->with('status', 'Resposta guardada e a ser indexada.');
    }

    public function dismiss(PendingQuestion $pending)
    {
        $pending->update(['status' => PendingStatus::Dismissed]);

        return redirect()->route('queue.index');
    }
}