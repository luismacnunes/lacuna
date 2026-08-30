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

        $type = (string) ($payload['answer_type'] ?? 'not_in_material');

        return new GeneratedAnswer(
            supported: $type !== 'not_in_material',
            content: (string) ($payload['answer'] ?? ''),
            answerType: $type,
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

        Devolve exclusivamente um objecto JSON com três campos:
        - "answer_type": um de "direct", "negative_rule", "not_in_material".
        - "supported": true ou false.
        - "answer": a resposta em português europeu.

        Como escolher o answer_type:
        - "direct": o material afirma explicitamente o que é pedido.
        - "negative_rule": o material define uma regra, um limite ou uma lista fechada, e o caso perguntado cai fora dela. A resposta é negativa mas é uma resposta.
        - "not_in_material": a informação pedida não está escrita no material, mesmo que o material trate do mesmo assunto.

        Define supported como true para "direct" e "negative_rule", e false para "not_in_material".

        Regras:
        - Nunca uses conhecimento exterior ao material. Se a resposta não estiver no material, o answer_type é "not_in_material", mesmo que saibas a resposta.
        - Não inventes números, prazos, nomes ou passos que não estejam escritos no material.
        - Quando o answer_type for "not_in_material", o campo "answer" é uma frase curta a dizer que o material disponível não cobre a pergunta.
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

    public function suggestTopicName(string $question): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => <<<'TXT'
                    Recebes uma pergunta feita a uma base de conhecimento interna. Devolve o nome do tema a que essa pergunta pertence.

                    Devolve um objecto JSON com um único campo "name".

                    Regras:
                    - Duas a quatro palavras, em português europeu.
                    - É o assunto, não a pergunta. Sem verbos interrogativos, sem pontos de interrogação.
                    - Usa a forma mais geral que ainda seja específica. "Garantias de peças", não "Garantia da peça aplicada no carro do cliente".
                    - Duas perguntas diferentes sobre o mesmo assunto têm de produzir o mesmo nome.
                    TXT],
                    ['role' => 'user', 'content' => $question],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao sugerir tema: ' . $response->body());
        }

        $payload = json_decode($response->json('choices.0.message.content'), true);

        return trim((string) ($payload['name'] ?? $question));
    }
}