<?php

namespace App\Services;

use App\Models\Topic;
use App\Services\Embeddings\EmbeddingProvider;
use Illuminate\Support\Facades\DB;

class TopicResolver
{
    private const MERGE_THRESHOLD = 0.85;

    public function __construct(
        private EmbeddingProvider $embeddings
    ) {}

    public function resolve(string $name, ?string $description = null): Topic
    {
        $vector = $this->embeddings->embed($name);

        $existing = $this->findByVector($vector);

        if ($existing) {
            return $existing;
        }

        $topic = Topic::create([
            'name' => $name,
            'description' => $description,
        ]);

        $this->storeEmbedding($topic, $vector);

        return $topic;
    }

    private function findByVector(array $vector): ?Topic
    {
        $literal = $this->toLiteral($vector);

        $row = DB::selectOne(
            'SELECT id, 1 - (embedding <=> ?::vector) AS similarity
             FROM topics
             WHERE embedding IS NOT NULL AND archived_at IS NULL
             ORDER BY embedding <=> ?::vector
             LIMIT 1',
            [$literal, $literal]
        );

        return $row && $row->similarity >= self::MERGE_THRESHOLD
            ? Topic::find($row->id)
            : null;
    }

    private function storeEmbedding(Topic $topic, array $vector): void
    {
        DB::statement(
            'UPDATE topics SET embedding = ?::vector WHERE id = ?',
            [$this->toLiteral($vector), $topic->id]
        );
    }

    private function toLiteral(array $vector): string
    {
        return '[' . implode(',', $vector) . ']';
    }
}