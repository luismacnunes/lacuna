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

    public function generateQuestions(string $title, string $description, string $content, int $max = 4): array
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->questionsPrompt($max)],
                    ['role' => 'user', 'content' => "Título: {$title}\nDescrição: {$description}\n\nConteúdo:\n{$content}"],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao gerar perguntas: ' . $response->body());
        }

        $payload = json_decode($response->json('choices.0.message.content'), true);

        return array_slice($payload['questions'] ?? [], 0, $max);
    }

    private function questionsPrompt(int $max): string
    {
        return <<<TXT
        Recebes um documento de uma base de conhecimento interna de uma equipa de desenvolvimento. A tua tarefa é identificar o que falta neste documento para ele ser útil a um colega daqui a seis meses.

        Devolve um objecto JSON com um único campo "questions": um array de no máximo {$max} perguntas em português europeu.

        Regras:
        - Pergunta apenas o que o documento NÃO responde. Se o documento já explica alguma coisa, não perguntes sobre isso.
        - Prefere perguntas sobre o porquê das decisões, sobre casos de excepção, e sobre o que fazer quando algo corre mal.
        - Não faças perguntas genéricas do tipo "qual é o objectivo deste documento" ou "quem é o responsável".
        - Cada pergunta tem de ser respondível por quem escreveu o documento, em poucas frases.
        - Se o documento estiver completo e não faltar nada relevante, devolve um array vazio.
        TXT;
    }
}