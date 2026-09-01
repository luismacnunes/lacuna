<?php

use App\Enums\FailureReason;
use App\Enums\PendingOrigin;
use App\Enums\PendingStatus;
use App\Enums\QuestionStatus;
use App\Models\Document;
use App\Models\PendingQuestion;
use App\Models\Topic;
use App\Services\AnswerQuestion;
use App\Services\Llm\LlmProvider;
use Tests\Support\FakeLlmProvider;

function seedDocument(string $topicName = 'Deploys'): Document
{
    $topic = Topic::create(['name' => $topicName, 'has_material' => true]);

    $document = Document::create([
        'topic_id' => $topic->id,
        'title' => 'Deploy para o cliente',
        'content' => 'A janela de deploy é de terça a quinta, entre as 20h e as 23h.',
        'source_type' => 'text',
        'content_hash' => hash('sha256', 'x'),
    ]);

    $chunk = $document->chunks()->create([
        'position' => 0,
        'content' => $document->content,
    ]);

    $vector = app(App\Services\Embeddings\EmbeddingProvider::class)->embed($chunk->content);

    DB::statement('UPDATE chunks SET embedding = ?::vector WHERE id = ?', [
        '[' . implode(',', $vector) . ']',
        $chunk->id,
    ]);

    return $document;
}

it('records a topic gap when nothing relevant is found', function () {
    app()->instance(LlmProvider::class, new FakeLlmProvider(topicName: 'Garantias de peças'));

    $question = app(AnswerQuestion::class)->handle('Que garantia damos nas peças?');

    expect($question->status)->toBe(QuestionStatus::Unanswered)
        ->and($question->failure_reason)->toBe(FailureReason::NoRelevantChunks);

    $topic = Topic::where('name', 'Garantias de peças')->first();

    expect($topic)->not->toBeNull()
        ->and($topic->has_material)->toBeFalse();

    expect(PendingQuestion::where('topic_id', $topic->id)->first())
        ->origin->toBe(PendingOrigin::RealFailure)
        ->status->toBe(PendingStatus::Open);
});

it('records an answer gap against the existing topic', function () {
    $document = seedDocument();

    app()->instance(LlmProvider::class, new FakeLlmProvider(answerType: 'not_in_material'));

    $question = app(AnswerQuestion::class)->handle('A janela de deploy é de terça a quinta?');

    expect($question->failure_reason)->toBe(FailureReason::NotSupported);

    expect(PendingQuestion::first()->topic_id)->toBe($document->topic_id);
});

it('does not create duplicate pending items for the same question', function () {
    app()->instance(LlmProvider::class, new FakeLlmProvider(topicName: 'Garantias'));

    $answerer = app(AnswerQuestion::class);
    $answerer->handle('Que garantia damos nas peças?');
    $answerer->handle('Que garantia damos nas peças?');

    expect(PendingQuestion::count())->toBe(1);
});

it('stores the answer with its sources when supported', function () {
    seedDocument();

    app()->instance(LlmProvider::class, new FakeLlmProvider(answerType: 'direct'));

    $question = app(AnswerQuestion::class)->handle('A janela de deploy é de terça a quinta?');

    expect($question->status)->toBe(QuestionStatus::Answered)
        ->and($question->answer->answer_type)->toBe('direct')
        ->and($question->answer->chunks)->toHaveCount(1);

    expect(PendingQuestion::count())->toBe(0);
});