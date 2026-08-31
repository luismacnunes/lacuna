<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['question_id', 'content', 'model', 'answer_type'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function chunks()
    {
        return $this->belongsToMany(Chunk::class)->withPivot('similarity');
    }

    public function curatedAnswers()
    {
        return $this->belongsToMany(CuratedAnswer::class)->withPivot('similarity');
    }
}
