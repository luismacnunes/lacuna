<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PendingOrigin;
use App\Enums\PendingStatus;

class PendingQuestion extends Model
{
    protected $fillable = [
        'topic_id', 'document_id', 'question_id', 'text',
        'origin', 'status', 'answered_by', 'curated_answer_id',
    ];

    protected $casts = [
        'origin' => PendingOrigin::class,
        'status' => PendingStatus::class,
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
