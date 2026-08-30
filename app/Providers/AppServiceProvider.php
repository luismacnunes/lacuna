<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Embeddings\EmbeddingProvider;
use App\Services\Embeddings\FakeEmbeddingProvider;
use App\Services\Embeddings\OpenAiEmbeddingProvider;
use App\Services\Llm\LlmProvider;
use App\Services\Llm\OpenAiLlmProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmbeddingProvider::class, function () {
            return match (config('lacuna.embedding_driver')) {
                'openai' => new OpenAiEmbeddingProvider(
                    config('services.openai.key'),
                    config('services.openai.embedding_model'),
                ),
                default => new FakeEmbeddingProvider(),
            };
        });
        $this->app->bind(LlmProvider::class, function () {
            return new OpenAiLlmProvider(
                config('services.openai.key'),
                config('services.openai.chat_model'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
