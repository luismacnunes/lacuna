<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Chunk;
use App\Services\Embeddings\EmbeddingProvider;
use Illuminate\Support\Facades\DB;

class GenerateChunkEmbedding implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(public Chunk $chunk) {}

    /**
     * Execute the job.
     */
    public function handle(EmbeddingProvider $embeddings): void
    {
        $vector = $embeddings->embed($this->chunk->content);

        DB::statement(
            'UPDATE chunks SET embedding = ?::vector WHERE id = ?',
            ['[' . implode(',', $vector) . ']', $this->chunk->id]
        );
    }
}
