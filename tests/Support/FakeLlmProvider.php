<?php

namespace Tests\Support;

use App\Services\Llm\GeneratedAnswer;
use App\Services\Llm\LlmProvider;

class FakeLlmProvider implements LlmProvider
{
    public function __construct(
        private string $answerType = 'direct',
        private string $topicName = 'Tema de teste',
        private array $questions = []
    ) {}

    public function answer(string $question, array $chunks): GeneratedAnswer
    {
        return new GeneratedAnswer(
            supported: $this->answerType !== 'not_in_material',
            content: 'Resposta de teste.',
            answerType: $this->answerType,
        );
    }

    public function generateQuestions(string $title, string $description, string $content, int $max = 4): array
    {
        return $this->questions;
    }

    public function suggestTopicName(string $question): string
    {
        return $this->topicName;
    }

    public function name(): string
    {
        return 'fake';
    }
}