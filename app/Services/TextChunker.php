<?php

namespace App\Services;

class TextChunker
{
    public function __construct(
        private int $targetSize = 800
    ) {}

    public function chunk(string $text): array
    {
        $paragraphs = preg_split('/\n\s*\n/', trim($text));
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            if ($current !== '' && strlen($current) + strlen($paragraph) > $this->targetSize) {
                $chunks[] = $current;
                $current = $paragraph;
            } else {
                $current = $current === '' ? $paragraph : $current . "\n\n" . $paragraph;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }
}