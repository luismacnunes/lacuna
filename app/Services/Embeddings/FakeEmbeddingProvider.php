<?php

namespace App\Services\Embeddings;

class FakeEmbeddingProvider implements EmbeddingProvider
{
    private const DIMENSIONS = 1536;

    public function embed(string $text): array
    {
        $vector = array_fill(0, self::DIMENSIONS, 0.0);

        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            $index = crc32($word) % self::DIMENSIONS;
            $vector[$index] += 1.0;
        }

        return $this->normalise($vector);
    }

    public function dimensions(): int
    {
        return self::DIMENSIONS;
    }

    private function normalise(array $vector): array
    {
        $magnitude = sqrt(array_sum(array_map(fn ($v) => $v * $v, $vector)));

        return $magnitude === 0.0
            ? $vector
            : array_map(fn ($v) => $v / $magnitude, $vector);
    }
}