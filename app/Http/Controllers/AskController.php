<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\AnswerQuestion;
use Illuminate\Http\Request;
use App\Models\Chunk;
use App\Models\Topic;

class AskController extends Controller
{
    public function index(Request $request)
    {
        $question = $request->filled('question')
        ? Question::with('answer.chunks.document', 'answer.curatedAnswers.topic')->find($request->integer('question'))
        : null;
        
        $topic = null;
        $pending = null;

        if ($question && ! $question->answer) {
            $pending = $question->pendingQuestion()->with('topic')->first();
            $topic = $pending?->topic;
        }

        return view('ask.index', [
            'question' => $question,
            'topic' => $topic,
            'pending' => $pending,
            'passages' => Chunk::count(),
            'subjects' => Topic::whereNull('archived_at')->count(),
        ]);
    }

    public function store(Request $request, AnswerQuestion $answerer)
    {
        $data = $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $question = $answerer->handle($data['text']);

        return redirect()->route('ask.index', ['question' => $question->id]);
    }
}