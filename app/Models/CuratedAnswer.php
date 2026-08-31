<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuratedAnswer extends Model
{
    protected $fillable = ['topic_id', 'author_id', 'question', 'answer', 'flagged_at', 'flagged_by_document_id', 'reviewed_at', 'reviewed_by'];

    protected $casts = [
        'flagged_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function flaggedByDocument()
    {
        return $this->belongsTo(Document::class, 'flagged_by_document_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeNeedingReview($query)
    {
        return $query->whereNotNull('flagged_at');
    }
}
