<?php

namespace App\Services;

use App\Enums\FailureReason;
use App\Enums\PendingOrigin;
use App\Enums\PendingStatus;
use App\Enums\QuestionStatus;
use App\Models\Chunk;
use App\Models\PendingQuestion;
use App\Models\Question;
use App\Models\Topic;
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

        $chunks = array_values(array_filter(
            $this->retriever->search($text, self::CHUNKS_TO_USE),
            fn ($chunk) => $chunk->similarity >= self::MINIMUM_SIMILARITY
        ));

        if ($chunks === []) {
            $question->update(['failure_reason' => FailureReason::NoRelevantChunks]);
            $this->recordTopicGap($question);

            return $question;
        }

        $generated = $this->llm->answer($text, $chunks);

        if (! $generated->supported) {
            $question->update(['failure_reason' => FailureReason::NotSupported]);
            $this->recordAnswerGap($question, $chunks[0]);

            return $question;
        }

        $answer = $question->answer()->create([
            'content' => $generated->content,
            'answer_type' => $generated->answerType,
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

    private function recordTopicGap(Question $question): void
    {
        $topic = Topic::create([
            'name' => $question->text,
            'has_material' => false,
        ]);

        $this->createPending($topic, $question);
    }

    private function recordAnswerGap(Question $question, object $closest): void
    {
        $topic = Chunk::find($closest->id)?->document?->topic;

        if (! $topic) {
            $this->recordTopicGap($question);

            return;
        }

        $this->createPending($topic, $question);
    }

    private function createPending(Topic $topic, Question $question): void
    {
        PendingQuestion::create([
            'topic_id' => $topic->id,
            'question_id' => $question->id,
            'text' => $question->text,
            'origin' => PendingOrigin::RealFailure,
            'status' => PendingStatus::Open,
        ]);
    }
}