<?php

namespace App\Jobs;

use App\Models\CuratedAnswer;
use App\Services\Embeddings\EmbeddingProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class GenerateCuratedAnswerEmbedding implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public CuratedAnswer $curatedAnswer) {}

    public function handle(EmbeddingProvider $embeddings): void
    {
        $text = $this->curatedAnswer->question . "\n\n" . $this->curatedAnswer->answer;

        $vector = $embeddings->embed($text);

        DB::statement(
            'UPDATE curated_answers SET embedding = ?::vector WHERE id = ?',
            ['[' . implode(',', $vector) . ']', $this->curatedAnswer->id]
        );
    }
}