<?php

use App\Services\TextChunker;

it('keeps short text as a single chunk', function () {
    $chunker = new TextChunker(800);

    $chunks = $chunker->chunk("Primeiro parágrafo.\n\nSegundo parágrafo.");

    expect($chunks)->toHaveCount(1);
});

it('splits when the target size is exceeded', function () {
    $chunker = new TextChunker(30);

    $chunks = $chunker->chunk("Primeiro parágrafo aqui.\n\nSegundo parágrafo aqui.\n\nTerceiro aqui.");

    expect(count($chunks))->toBeGreaterThan(1);
});

it('never splits inside a paragraph', function () {
    $chunker = new TextChunker(10);
    $paragraph = 'Um parágrafo bastante longo que passa do limite sozinho.';

    $chunks = $chunker->chunk($paragraph);

    expect($chunks)->toBe([$paragraph]);
});

it('ignores empty paragraphs', function () {
    $chunker = new TextChunker(800);

    $chunks = $chunker->chunk("Primeiro.\n\n\n\n   \n\nSegundo.");

    expect($chunks)->toHaveCount(1);
});