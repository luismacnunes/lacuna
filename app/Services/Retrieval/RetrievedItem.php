<?php

namespace App\Services\Retrieval;

class RetrievedItem
{
    public function __construct(
        public readonly string $source,
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly float $similarity,
        public readonly float $rank
    ) {}

    public function isCurated(): bool
    {
        return $this->source === 'curated';
    }
}