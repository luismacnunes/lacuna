<?php

namespace App\Services;

use App\Models\Topic;
use App\Services\Embeddings\EmbeddingProvider;
use Illuminate\Support\Facades\DB;

class TopicResolver
{
    private const MERGE_THRESHOLD = 0.80;

    public function __construct(
        private EmbeddingProvider $embeddings
    ) {}

    public function resolve(string $name, ?string $description = null): Topic
    {
        $existing = $this->findSimilar($name . "\n" . $description);

        if ($existing) {
            return $existing;
        }

        return Topic::create([
            'name' => $name,
            'description' => $description,
        ]);
    }

    public function findSimilar(string $text): ?Topic
    {
        $vector = '[' . implode(',', $this->embeddings->embed($text)) . ']';

        $row = DB::selectOne(
            'SELECT t.id, 1 - (ca.embedding <=> ?::vector) AS similarity
             FROM topics t
             JOIN curated_answers ca ON ca.topic_id = t.id
             WHERE ca.embedding IS NOT NULL AND t.archived_at IS NULL
             ORDER BY ca.embedding <=> ?::vector
             LIMIT 1',
            [$vector, $vector]
        );

        return $row && $row->similarity >= self::MERGE_THRESHOLD
            ? Topic::find($row->id)
            : null;
    }
}