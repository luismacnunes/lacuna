<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\AnswerQuestion;
use Illuminate\Http\Request;

class AskController extends Controller
{
    public function index(Request $request)
    {
        $question = $request->filled('question')
            ? Question::with('answer.chunks.document')->find($request->integer('question'))
            : null;

        return view('ask.index', ['question' => $question]);
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