<?php

namespace App\Http\Controllers;

use App\Services\CoverageReport;

class MetricsController extends Controller
{
    public function index(CoverageReport $report)
    {
        return view('metrics.index', [
            'weeks' => $report->weekly(),
        ]);
    }
}