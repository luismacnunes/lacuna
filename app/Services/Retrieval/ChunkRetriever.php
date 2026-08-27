<?php

namespace App\Services\Retrieval;

use App\Services\Embeddings\EmbeddingProvider;
use Illuminate\Support\Facades\DB;

class ChunkRetriever
{
    public function __construct(
        private EmbeddingProvider $embeddings
    ) {}

    public function search(string $query, int $limit = 5): array
    {
        $vector = '[' . implode(',', $this->embeddings->embed($query)) . ']';

        return DB::select(
            'SELECT c.id, c.content, d.title,
                    1 - (c.embedding <=> ?::vector) AS similarity
             FROM chunks c
             JOIN documents d ON d.id = c.document_id
             WHERE c.embedding IS NOT NULL
             ORDER BY c.embedding <=> ?::vector
             LIMIT ' . (int) $limit,
            [$vector, $vector]
        );
    }
}