<?php

namespace App\Services\Embeddings;

interface EmbeddingProvider
{
    public function embed(string $text): array;

    public function dimensions(): int;
}