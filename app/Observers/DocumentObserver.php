<?php

namespace App\Observers;

use App\Models\CuratedAnswer;
use App\Models\Document;

class DocumentObserver
{
    public function updated(Document $document): void
    {
        if (! $document->wasChanged('content_hash')) {
            return;
        }

        if (! $document->topic_id) {
            return;
        }

        CuratedAnswer::where('topic_id', $document->topic_id)
            ->whereNull('flagged_at')
            ->update([
                'flagged_at' => now(),
                'flagged_by_document_id' => $document->id,
            ]);
    }
}