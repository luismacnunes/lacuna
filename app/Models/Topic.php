<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Document;

class Topic extends Model
{
    protected $fillable = ['name', 'description', 'owner_id', 'has_material', 'archived_at'];

    protected $casts = [
        'has_material' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function pendingQuestions()
    {
        return $this->hasMany(PendingQuestion::class);
    }

    public function curatedAnswers()
    {
        return $this->hasMany(CuratedAnswer::class);
    }
}
