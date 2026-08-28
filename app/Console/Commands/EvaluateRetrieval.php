<?php

namespace App\Console\Commands;

use App\Services\Retrieval\ChunkRetriever;
use Illuminate\Console\Command;

class EvaluateRetrieval extends Command
{
    protected $signature = 'lacuna:eval {--file=tests/Fixtures/eval_questions.json}';

    protected $description = 'Corre as perguntas de avaliação e mede a qualidade da recuperação';

    public function handle(ChunkRetriever $retriever): int
    {
        $path = base_path($this->option('file'));

        if (! file_exists($path)) {
            $this->error("Ficheiro não encontrado: {$path}");

            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($path), true);
        $questions = $data['answerable'] ?? [];

        $hitsAtOne = 0;
        $hitsAtThree = 0;
        $rows = [];

        foreach ($questions as $item) {
            $results = $retriever->search($item['question'], 3);
            $titles = array_map(fn ($r) => $r->title, $results);

            $atOne = isset($titles[0]) && $titles[0] === $item['expect'];
            $atThree = in_array($item['expect'], $titles, true);

            $hitsAtOne += $atOne ? 1 : 0;
            $hitsAtThree += $atThree ? 1 : 0;

            $rows[] = [
                $atThree ? 'OK' : 'FALHA',
                mb_strimwidth($item['question'], 0, 55, '...'),
                mb_strimwidth($titles[0] ?? '-', 0, 40, '...'),
                isset($results[0]) ? number_format($results[0]->similarity, 3) : '-',
            ];
        }

        $total = count($questions);

        $this->table(['', 'Pergunta', 'Primeiro resultado', 'Sem.'], $rows);

        $this->newLine();
        $this->line("Perguntas: {$total}");
        $this->line(sprintf('Acerto no 1.º resultado: %d/%d (%.0f%%)', $hitsAtOne, $total, $total ? $hitsAtOne / $total * 100 : 0));
        $this->line(sprintf('Acerto nos 3 primeiros: %d/%d (%.0f%%)', $hitsAtThree, $total, $total ? $hitsAtThree / $total * 100 : 0));
        $this->newLine();

        if ($total && $hitsAtThree / $total >= 0.7) {
            $this->info('Critério cumprido: recuperação boa o suficiente para avançar.');
        } else {
            $this->warn('Critério não cumprido: rever chunking ou modelo de embeddings antes de avançar.');
        }

        return self::SUCCESS;
    }
}
