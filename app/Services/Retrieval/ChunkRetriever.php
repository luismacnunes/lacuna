<?php

namespace App\Services\Retrieval;

use App\Services\Embeddings\EmbeddingProvider;
use Illuminate\Support\Facades\DB;

class ChunkRetriever
{
    private const CURATED_BOOST = 0.15;
    private const BOOST_FLOOR = 0.45;

    public function __construct(
        private EmbeddingProvider $embeddings
    ) {}

    public function search(string $query, int $limit = 5): array
    {
        $vector = '[' . implode(',', $this->embeddings->embed($query)) . ']';

        $results = array_merge(
            $this->searchCurated($vector, $limit),
            $this->searchChunks($vector, $limit)
        );

        usort($results, fn ($a, $b) => $b->rank <=> $a->rank);

        return array_slice($results, 0, $limit);
    }

    private function searchCurated(string $vector, int $limit): array
    {
        $rows = DB::select(
            'SELECT ca.id, ca.question, ca.answer, t.name AS title,
                    1 - (ca.embedding <=> ?::vector) AS similarity
            FROM curated_answers ca
            JOIN topics t ON t.id = ca.topic_id
            WHERE ca.embedding IS NOT NULL
            ORDER BY ca.embedding <=> ?::vector
            LIMIT ' . (int) $limit,
            [$vector, $vector]
        );

        return array_map(fn ($row) => new RetrievedItem(
            source: 'curated',
            id: $row->id,
            title: $row->title,
            content: $row->question . "\n\n" . $row->answer,
            similarity: (float) $row->similarity,
            rank: (float) $row->similarity + ((float) $row->similarity >= self::BOOST_FLOOR ? self::CURATED_BOOST : 0.0),
        ), $rows);
    }

    private function searchChunks(string $vector, int $limit): array
    {
        $rows = DB::select(
            'SELECT c.id, c.content, d.title,
                    1 - (c.embedding <=> ?::vector) AS similarity
            FROM chunks c
            JOIN documents d ON d.id = c.document_id
            WHERE c.embedding IS NOT NULL
            ORDER BY c.embedding <=> ?::vector
            LIMIT ' . (int) $limit,
            [$vector, $vector]
        );

        return array_map(fn ($row) => new RetrievedItem(
            source: 'chunk',
            id: $row->id,
            title: $row->title,
            content: $row->content,
            similarity: (float) $row->similarity,
            rank: (float) $row->similarity,
        ), $rows);
    }
}