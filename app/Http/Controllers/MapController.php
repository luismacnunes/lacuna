<?php

namespace App\Http\Controllers;

use App\Enums\QuestionStatus;
use App\Models\Chunk;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Support\Collection;

class MapController extends Controller
{
    private const WIDTH = 1000;
    private const HEIGHT = 620;

    /** Beyond this many passages, draw a sample. Two thousand dots read the
     *  same as five hundred, and the extra ones only cost render time. */
    private const MAX_POINTS = 600;

    /** A gap further than this from any passage is unwritten territory. */
    private const FRONTIER_DISTANCE = 0.10;

    public function index()
    {
        $chunks = Chunk::query()
            ->whereNotNull('map_x')
            ->with('document.topic')
            ->get(['id', 'document_id', 'map_x', 'map_y']);

        $gaps = Question::query()
            ->whereNotNull('map_x')
            ->where('status', QuestionStatus::Unanswered)
            ->with('pendingQuestion.topic')
            ->get(['id', 'text', 'map_x', 'map_y']);

        $this->classify($gaps, $chunks);

        $bounds = $this->bounds($chunks, $gaps);
        $sx = fn ($x) => ($x - $bounds['minX']) / ($bounds['maxX'] - $bounds['minX']) * self::WIDTH;
        $sy = fn ($y) => self::HEIGHT - ($y - $bounds['minY']) / ($bounds['maxY'] - $bounds['minY']) * self::HEIGHT;

        $clusters = $this->clusters($chunks, $sx, $sy);

        return view('map.index', [
            'points' => $this->points($chunks, $sx, $sy),
            'gaps' => $this->placedGaps($gaps, $sx, $sy),
            'clusters' => $this->withoutCollisions($clusters),
            'total' => $chunks->count(),
            'subjects' => Topic::whereNull('archived_at')->count(),
            'width' => self::WIDTH,
            'height' => self::HEIGHT,
        ]);
    }

    /**
     * A gap with nothing within reach is unwritten territory. One with
     * material around it is a hole in something already covered.
     */
    private function classify(Collection $gaps, Collection $chunks): void
    {
        $gaps->each(function ($gap) use ($chunks) {
            $nearest = $chunks->min(fn ($c) => sqrt(
                ($c->map_x - $gap->map_x) ** 2 + ($c->map_y - $gap->map_y) ** 2
            ));

            $gap->isolated = $nearest === null || $nearest > self::FRONTIER_DISTANCE;
        });
    }

    private function points(Collection $chunks, callable $sx, callable $sy): Collection
    {
        $sample = $chunks->count() > self::MAX_POINTS
            ? $chunks->shuffle()->take(self::MAX_POINTS)
            : $chunks;

        return $sample->map(fn ($c) => [
            'x' => round($sx($c->map_x), 1),
            'y' => round($sy($c->map_y), 1),
        ])->values();
    }

    private function placedGaps(Collection $gaps, callable $sx, callable $sy): Collection
    {
        return $gaps->map(fn ($gap) => [
            'x' => round($sx($gap->map_x), 1),
            'y' => round($sy($gap->map_y), 1),
            'text' => $gap->text,
            'isolated' => $gap->isolated,
            'topic' => $gap->pendingQuestion?->topic?->name,
        ])->values();
    }

    /**
     * One ellipse per subject, covering where its passages actually sit.
     * Gives each label a home instead of leaving it floating, and stays
     * readable when there are too many points to tell apart.
     */
    private function clusters(Collection $chunks, callable $sx, callable $sy): Collection
    {
        return $chunks
            ->filter(fn ($c) => $c->document?->topic)
            ->groupBy(fn ($c) => $c->document->topic->id)
            ->map(fn ($group) => [
                'name' => $group->first()->document->topic->name,
                'size' => $group->count(),
                'cx' => round($group->map(fn ($c) => $sx($c->map_x))->avg(), 1),
                'labelY' => round($group->map(fn ($c) => $sy($c->map_y))->max() + 22, 1),
            ])
            ->sortByDesc('size')
            ->values();
    }

    /**
     * Walk the subjects biggest first and drop any label that would land on
     * one already placed. Losing a small subject's name beats an unreadable
     * pile of overlapping text.
     */
    private function withoutCollisions(Collection $clusters): Collection
    {
        $placed = [];

        return $clusters->map(function ($cluster) use (&$placed) {
            $halfWidth = strlen($cluster['name']) * 3.4;

            $box = [
                'x1' => $cluster['cx'] - $halfWidth,
                'x2' => $cluster['cx'] + $halfWidth,
                'y1' => $cluster['labelY'] - 9,
                'y2' => $cluster['labelY'] + 5,
            ];

            foreach ($placed as $other) {
                $overlaps = $box['x1'] < $other['x2'] && $box['x2'] > $other['x1']
                    && $box['y1'] < $other['y2'] && $box['y2'] > $other['y1'];

                if ($overlaps) {
                    $cluster['showLabel'] = false;

                    return $cluster;
                }
            }

            $placed[] = $box;
            $cluster['showLabel'] = true;

            return $cluster;
        });
    }

    private function deviation(Collection $values, float $mean): float
    {
        if ($values->count() < 2) {
            return 0;
        }

        return sqrt($values->map(fn ($v) => ($v - $mean) ** 2)->sum() / $values->count());
    }

    private function bounds(Collection $chunks, Collection $gaps): array
    {
        $xs = $chunks->pluck('map_x')->merge($gaps->pluck('map_x'));
        $ys = $chunks->pluck('map_y')->merge($gaps->pluck('map_y'));

        if ($xs->isEmpty()) {
            return ['minX' => -1, 'maxX' => 1, 'minY' => -1, 'maxY' => 1];
        }

        $padX = ($xs->max() - $xs->min()) * 0.14 ?: 0.1;
        $padY = ($ys->max() - $ys->min()) * 0.18 ?: 0.1;

        return [
            'minX' => $xs->min() - $padX,
            'maxX' => $xs->max() + $padX,
            'minY' => $ys->min() - $padY,
            'maxY' => $ys->max() + $padY,
        ];
    }
}