<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\QuestionStatus;
use App\Enums\FailureReason;

class Question extends Model
{
    protected $fillable = ['text', 'status', 'failure_reason'];

    protected $casts = [
        'status' => QuestionStatus::class,
        'failure_reason' => FailureReason::class,
    ];

    public function answer()
    {
        return $this->hasOne(Answer::class);
    }

    public function pendingQuestion()
    {
        return $this->hasOne(PendingQuestion::class);
    }
}
