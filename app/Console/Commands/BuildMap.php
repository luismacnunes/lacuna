<?php

namespace App\Console\Commands;

use App\Enums\QuestionStatus;
use App\Services\Projection\PrincipalComponents;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BuildMap extends Command
{
    protected $signature = 'lacuna:map';

    protected $description = 'Project every chunk and unanswered question onto two dimensions';

    public function handle(PrincipalComponents $pca): int
    {
        $chunks = DB::select('SELECT id, embedding FROM chunks WHERE embedding IS NOT NULL');

        if (count($chunks) < 3) {
            $this->warn('Not enough indexed material to build a map.');

            return self::SUCCESS;
        }

        $vectors = array_map(fn ($row) => $this->parse($row->embedding), $chunks);

        $this->info('Finding the two directions the material varies along...');
        $basis = $pca->fit($vectors);

        // Chunks first: they define the space the questions get placed in.
        foreach ($chunks as $i => $chunk) {
            [$x, $y] = $pca->project($vectors[$i], $basis);

            DB::update('UPDATE chunks SET map_x = ?, map_y = ? WHERE id = ?', [$x, $y, $chunk->id]);
        }

        $this->info(count($chunks) . ' passages placed.');

        // Then the questions nobody could answer, projected onto the same
        // basis so their position is comparable.
        $gaps = DB::select(
            "SELECT q.id, q.text FROM questions q WHERE q.status = ? AND q.map_x IS NULL",
            [QuestionStatus::Unanswered->value]
        );

        $embeddings = app(\App\Services\Embeddings\EmbeddingProvider::class);
        $placed = 0;

        foreach ($gaps as $gap) {
            [$x, $y] = $pca->project($embeddings->embed($gap->text), $basis);

            DB::update('UPDATE questions SET map_x = ?, map_y = ? WHERE id = ?', [$x, $y, $gap->id]);
            $placed++;
        }

        $this->info($placed . ' gaps placed.');

        return self::SUCCESS;
    }

    /**
     * pgvector hands back the vector as a string like "[0.1,0.2,...]".
     */
    private function parse(string $raw): array
    {
        return array_map('floatval', explode(',', trim($raw, '[]')));
    }
}