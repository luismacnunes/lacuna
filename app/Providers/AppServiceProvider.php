<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Embeddings\EmbeddingProvider;
use App\Services\Embeddings\FakeEmbeddingProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmbeddingProvider::class, FakeEmbeddingProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
