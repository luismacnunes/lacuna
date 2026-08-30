<?php

namespace App\Http\Controllers;

use App\Enums\PendingStatus;
use App\Models\Topic;

class QueueController extends Controller
{
    public function index()
    {
        $topics = Topic::query()
            ->whereNull('archived_at')
            ->whereHas('pendingQuestions', fn ($q) => $q->where('status', PendingStatus::Open))
            ->with([
                'owner',
                'pendingQuestions' => fn ($q) => $q
                    ->where('status', PendingStatus::Open)
                    ->orderByRaw("case when origin = 'real_failure' then 0 else 1 end")
                    ->orderBy('created_at'),
            ])
            ->withCount(['pendingQuestions' => fn ($q) => $q->where('status', PendingStatus::Open)])
            ->orderBy('has_material')
            ->get();

        return view('queue.index', ['topics' => $topics]);
    }
}