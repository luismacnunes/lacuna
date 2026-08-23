<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Document;

class Topic extends Model
{
    protected $fillable = ['name', 'description'];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
