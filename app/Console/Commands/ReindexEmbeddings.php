<?php

namespace App\Console\Commands;

use App\Jobs\GenerateChunkEmbedding;
use App\Models\Chunk;
use Illuminate\Console\Command;

class ReindexEmbeddings extends Command
{
    protected $signature = 'lacuna:reindex';

    protected $description = 'Volta a gerar os embeddings de todos os chunks';

    public function handle(): int
    {
        Chunk::query()->update(['embedding' => null]);

        $count = 0;

        foreach (Chunk::lazy() as $chunk) {
            GenerateChunkEmbedding::dispatch($chunk);
            $count++;
        }

        $this->info("{$count} chunks marcados para reindexação.");

        return self::SUCCESS;
    }
}