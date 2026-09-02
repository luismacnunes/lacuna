<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCuratedAnswerEmbedding;
use App\Models\CuratedAnswer;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $answers = CuratedAnswer::needingReview()
            ->with(['topic', 'flaggedByDocument', 'author'])
            ->orderBy('flagged_at')
            ->get();

        return view('review.index', ['answers' => $answers]);
    }

    public function confirm(Request $request, CuratedAnswer $curatedAnswer)
    {
        $curatedAnswer->update([
            'flagged_at' => null,
            'flagged_by_document_id' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Resposta confirmada.');
    }

    public function update(Request $request, CuratedAnswer $curatedAnswer)
    {
        $data = $request->validate([
            'answer' => 'required|string|min:10',
        ]);

        $curatedAnswer->update([
            'answer' => $data['answer'],
            'flagged_at' => null,
            'flagged_by_document_id' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        GenerateCuratedAnswerEmbedding::dispatch($curatedAnswer);

        return redirect()->route('review.index')->with('status', 'Resposta corrigida e a ser reindexada.');
    }

    public function destroy(CuratedAnswer $curatedAnswer)
    {
        $curatedAnswer->delete();

        return back()->with('status', 'Resposta removida.');
    }

    public function edit(CuratedAnswer $curatedAnswer)
    {
        $curatedAnswer->load(['topic', 'flaggedByDocument']);

        return view('review.edit', ['answer' => $curatedAnswer]);
    }
}