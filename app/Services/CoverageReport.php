<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CoverageReport
{
    public function weekly(int $weeks = 12): Collection
    {
        $since = CarbonImmutable::now()->subWeeks($weeks)->startOfWeek();

        $rows = DB::select(
            "SELECT date_trunc('week', created_at) AS week,
                    count(*) AS total,
                    count(*) FILTER (WHERE status = 'answered') AS answered
             FROM questions
             WHERE created_at >= ?
             GROUP BY 1
             ORDER BY 1",
            [$since]
        );

        return collect($rows)->map(fn ($row) => [
            'week' => CarbonImmutable::parse($row->week),
            'total' => (int) $row->total,
            'answered' => (int) $row->answered,
            'rate' => $row->total > 0 ? (int) round($row->answered / $row->total * 100) : 0,
        ]);
    }
}