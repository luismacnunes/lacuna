<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Document;

class Chunk extends Model
{
    protected $fillable = ['document_id', 'position', 'content'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
