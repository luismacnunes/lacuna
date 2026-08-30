<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuratedAnswer extends Model
{
    protected $fillable = ['topic_id', 'author_id', 'question', 'answer'];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
