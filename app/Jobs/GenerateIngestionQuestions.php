<?php

namespace App\Jobs;

use App\Enums\PendingOrigin;
use App\Enums\PendingStatus;
use App\Models\Document;
use App\Models\PendingQuestion;
use App\Services\Llm\LlmProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateIngestionQuestions implements ShouldQueue
{
    use Queueable;
    
    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public Document $document) {}

    public function handle(LlmProvider $llm): void
    {
        if (! $this->document->topic_id) {
            return;
        }

        $questions = $llm->generateQuestions(
            $this->document->title,
            (string) $this->document->description,
            $this->document->content,
        );

        foreach ($questions as $text) {
            PendingQuestion::create([
                'topic_id' => $this->document->topic_id,
                'document_id' => $this->document->id,
                'text' => $text,
                'origin' => PendingOrigin::Ingestion,
                'status' => PendingStatus::Open,
            ]);
        }
    }
}
