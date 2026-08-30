<?php

namespace App\Services\Llm;

class GeneratedAnswer
{
    public function __construct(
        public readonly bool $supported,
        public readonly string $content
    ) {}
}