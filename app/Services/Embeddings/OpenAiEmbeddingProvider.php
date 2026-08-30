<?php

namespace App\Services\Embeddings;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiEmbeddingProvider implements EmbeddingProvider
{
    private const DIMENSIONS = 1536;

    public function __construct(
        private string $apiKey,
        private string $model
    ) {}

    public function embed(string $text): array
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $this->model,
                'input' => $text,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao gerar embedding: ' . $response->body());
        }

        return $response->json('data.0.embedding');
    }

    public function dimensions(): int
    {
        return self::DIMENSIONS;
    }
}