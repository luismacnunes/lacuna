<?php

namespace App\Services;

use App\Enums\FailureReason;
use App\Enums\QuestionStatus;
use App\Models\Question;
use App\Services\Llm\LlmProvider;
use App\Services\Retrieval\ChunkRetriever;

class AnswerQuestion
{
    private const MINIMUM_SIMILARITY = 0.30;
    private const CHUNKS_TO_USE = 5;

    public function __construct(
        private ChunkRetriever $retriever,
        private LlmProvider $llm
    ) {}

    public function handle(string $text): Question
    {
        $question = Question::create([
            'text' => $text,
            'status' => QuestionStatus::Unanswered,
        ]);

        $chunks = array_filter(
            $this->retriever->search($text, self::CHUNKS_TO_USE),
            fn ($chunk) => $chunk->similarity >= self::MINIMUM_SIMILARITY
        );

        if ($chunks === []) {
            $question->update(['failure_reason' => FailureReason::NoRelevantChunks]);

            return $question;
        }

        $generated = $this->llm->answer($text, $chunks);

        if (! $generated->supported) {
            $question->update(['failure_reason' => FailureReason::NotSupported]);

            return $question;
        }

        $answer = $question->answer()->create([
            'content' => $generated->content,
            'model' => $this->llm->name(),
        ]);

        $answer->chunks()->attach(
            collect($chunks)->mapWithKeys(fn ($c) => [$c->id => ['similarity' => $c->similarity]])->all()
        );

        $question->update([
            'status' => QuestionStatus::Answered,
            'failure_reason' => null,
        ]);

        return $question;
    }
}