<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiLlmProvider implements LlmProvider
{
    public function __construct(
        private string $apiKey,
        private string $model
    ) {}

    public function answer(string $question, array $chunks): GeneratedAnswer
    {
        $context = '';

        foreach ($chunks as $i => $chunk) {
            $n = $i + 1;
            $context .= "[Trecho {$n} — {$chunk->title}]\n{$chunk->content}\n\n";
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => "Pergunta:\n{$question}\n\nMaterial disponível:\n{$context}"],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao gerar resposta: ' . $response->body());
        }

        $payload = json_decode($response->json('choices.0.message.content'), true);

        return new GeneratedAnswer(
            supported: (bool) ($payload['supported'] ?? false),
            content: (string) ($payload['answer'] ?? ''),
        );
    }

    public function name(): string
    {
        return $this->model;
    }

    private function systemPrompt(): string
    {
        return <<<'TXT'
        És o motor de respostas de uma base de conhecimento interna. Respondes apenas com base no material fornecido.

        Devolve exclusivamente um objecto JSON com dois campos:
        - "supported": true se o material fornecido contém a informação necessária para responder à pergunta; false caso contrário.
        - "answer": a resposta em português europeu, quando supported for true. Quando supported for false, uma frase curta a dizer que o material disponível não cobre a pergunta.

        Regras:
        - Nunca uses conhecimento exterior ao material. Se a resposta não estiver no material, supported é false, mesmo que saibas a resposta.
        - Material que trata do mesmo tema mas não permite responder à pergunta concreta não conta como suporte. Nesse caso, supported é false.
        - Uma resposta negativa é uma resposta. Se o material define uma regra, um limite ou uma lista fechada, e a pergunta cai fora dela, supported é true e respondes que não, citando a regra.
        - Não inventes números, prazos, nomes ou passos que não estejam escritos no material.
        - Quando responderes, indica entre parênteses o número do trecho em que te baseaste.
        - Sê directo e conciso.
        TXT;
    }
}