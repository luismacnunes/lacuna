<?php

namespace App\Services;

use App\Enums\FailureReason;
use App\Enums\PendingOrigin;
use App\Enums\PendingStatus;
use App\Enums\QuestionStatus;
use App\Models\Chunk;
use App\Models\CuratedAnswer;
use App\Models\PendingQuestion;
use App\Models\Question;
use App\Models\Topic;
use App\Services\Llm\LlmProvider;
use App\Services\Retrieval\ChunkRetriever;
use App\Services\Retrieval\RetrievedItem;

class AnswerQuestion
{
    private const MINIMUM_SIMILARITY = 0.30;
    private const ITEMS_TO_USE = 5;

    public function __construct(
        private ChunkRetriever $retriever,
        private LlmProvider $llm,
        private TopicResolver $topics
    ) {}

    public function handle(string $text): Question
    {
        $question = Question::create([
            'text' => $text,
            'status' => QuestionStatus::Unanswered,
        ]);

        $items = array_values(array_filter(
            $this->retriever->search($text, self::ITEMS_TO_USE),
            fn (RetrievedItem $item) => $item->similarity >= self::MINIMUM_SIMILARITY
        ));

        if ($items === []) {
            $question->update(['failure_reason' => FailureReason::NoRelevantChunks]);
            $this->recordTopicGap($question);

            return $question;
        }

        $generated = $this->llm->answer($text, $items);

        if (! $generated->supported) {
            $question->update(['failure_reason' => FailureReason::NotSupported]);
            $this->recordAnswerGap($question, $items[0]);

            return $question;
        }

        $answer = $question->answer()->create([
            'content' => $generated->content,
            'answer_type' => $generated->answerType,
            'model' => $this->llm->name(),
        ]);

        $this->attachSources($answer, $items);

        $question->update([
            'status' => QuestionStatus::Answered,
            'failure_reason' => null,
        ]);

        return $question;
    }

    private function attachSources(\App\Models\Answer $answer, array $items): void
    {
        $curated = array_filter($items, fn (RetrievedItem $item) => $item->isCurated());
        $chunks = array_filter($items, fn (RetrievedItem $item) => ! $item->isCurated());

        if ($curated !== []) {
            $answer->curatedAnswers()->attach(
                collect($curated)->mapWithKeys(fn (RetrievedItem $i) => [$i->id => ['similarity' => $i->similarity]])->all()
            );
        }

        if ($chunks !== []) {
            $answer->chunks()->attach(
                collect($chunks)->mapWithKeys(fn (RetrievedItem $i) => [$i->id => ['similarity' => $i->similarity]])->all()
            );
        }
    }

    private function recordTopicGap(Question $question): void
    {
        $name = $this->llm->suggestTopicName($question->text);

        $topic = $this->topics->resolve($name);

        if (! $topic->wasRecentlyCreated && $topic->has_material) {
            $this->createPending($topic, $question);

            return;
        }

        $topic->update(['has_material' => false]);

        $this->createPending($topic, $question);
    }

    private function recordAnswerGap(Question $question, RetrievedItem $closest): void
    {
        $topic = $closest->isCurated()
            ? CuratedAnswer::find($closest->id)?->topic
            : Chunk::find($closest->id)?->document?->topic;

        if (! $topic) {
            $this->recordTopicGap($question);

            return;
        }

        $this->createPending($topic, $question);
    }

    private function createPending(Topic $topic, Question $question): void
    {
        if ($this->hasSimilarPending($topic, $question->text)) {
            return;
        }

        PendingQuestion::create([
            'topic_id' => $topic->id,
            'question_id' => $question->id,
            'text' => $question->text,
            'origin' => PendingOrigin::RealFailure,
            'status' => PendingStatus::Open,
        ]);
    }

    private function hasSimilarPending(Topic $topic, string $text): bool
    {
        $normalised = mb_strtolower(trim($text));

        return $topic->pendingQuestions()
            ->where('status', PendingStatus::Open)
            ->get(['text'])
            ->contains(fn ($p) => mb_strtolower(trim($p->text)) === $normalised);
    }
}