<?php

namespace App\Services\Llm;

interface LlmProvider
{
    public function answer(string $question, array $chunks): GeneratedAnswer;

    public function name(): string;

    public function generateQuestions(string $title, string $description, string $content, int $max = 4): array;

    public function suggestTopicName(string $question): string;
}