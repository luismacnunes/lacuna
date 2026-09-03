<?php

namespace App\Services\Projection;

class PrincipalComponents
{
    /**
     * Reduce a set of high-dimensional vectors to two dimensions.
     *
     * Finds the two directions along which the data varies most, using
     * power iteration, then projects every vector onto them. Cruder than
     * UMAP, but it needs no dependencies and it separates clusters well
     * enough to read a map by.
     *
     * @param  array<int, array<int, float>>  $vectors
     * @return array{components: array<int, array<int, float>>, mean: array<int, float>}
     */
    public function fit(array $vectors, int $iterations = 60): array
    {
        $mean = $this->mean($vectors);
        $centred = array_map(fn ($v) => $this->subtract($v, $mean), $vectors);

        $first = $this->dominantDirection($centred, $iterations);

        // Remove the first component from the data, so the second
        // iteration finds a direction perpendicular to it.
        $remainder = array_map(function ($v) use ($first) {
            $projection = $this->dot($v, $first);

            return $this->subtract($v, $this->scale($first, $projection));
        }, $centred);

        $second = $this->dominantDirection($remainder, $iterations);

        return ['components' => [$first, $second], 'mean' => $mean];
    }

    /**
     * @param  array<int, float>  $vector
     * @param  array{components: array<int, array<int, float>>, mean: array<int, float>}  $basis
     * @return array{0: float, 1: float}
     */
    public function project(array $vector, array $basis): array
    {
        $centred = $this->subtract($vector, $basis['mean']);

        return [
            $this->dot($centred, $basis['components'][0]),
            $this->dot($centred, $basis['components'][1]),
        ];
    }

    private function dominantDirection(array $vectors, int $iterations): array
    {
        $dimensions = count($vectors[0]);

        // Start from a fixed vector rather than a random one, so the map
        // comes out the same every time it's rebuilt.
        $direction = array_fill(0, $dimensions, 1 / sqrt($dimensions));

        for ($i = 0; $i < $iterations; $i++) {
            $next = array_fill(0, $dimensions, 0.0);

            foreach ($vectors as $vector) {
                $weight = $this->dot($vector, $direction);

                for ($d = 0; $d < $dimensions; $d++) {
                    $next[$d] += $weight * $vector[$d];
                }
            }

            $direction = $this->normalise($next);
        }

        return $direction;
    }

    private function mean(array $vectors): array
    {
        $dimensions = count($vectors[0]);
        $sum = array_fill(0, $dimensions, 0.0);

        foreach ($vectors as $vector) {
            for ($d = 0; $d < $dimensions; $d++) {
                $sum[$d] += $vector[$d];
            }
        }

        $count = count($vectors);

        return array_map(fn ($s) => $s / $count, $sum);
    }

    private function subtract(array $a, array $b): array
    {
        $out = [];

        for ($i = 0; $i < count($a); $i++) {
            $out[$i] = $a[$i] - $b[$i];
        }

        return $out;
    }

    private function scale(array $v, float $by): array
    {
        return array_map(fn ($x) => $x * $by, $v);
    }

    private function dot(array $a, array $b): float
    {
        $sum = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $sum += $a[$i] * $b[$i];
        }

        return $sum;
    }

    private function normalise(array $v): array
    {
        $magnitude = sqrt($this->dot($v, $v));

        return $magnitude > 0 ? array_map(fn ($x) => $x / $magnitude, $v) : $v;
    }
}