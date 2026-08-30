<?php

namespace App\Services\Llm;

interface LlmProvider
{
    public function answer(string $question, array $chunks): GeneratedAnswer;

    public function name(): string;
}