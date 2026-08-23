<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Topic;
use App\Models\Chunk;

class Document extends Model
{
    protected $fillable = ['topic_id', 'title', 'description', 'source_type', 'original_filename', 'storage_path', 'content', 'content_hash'];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function chunks()
    {
        return $this->hasMany(Chunk::class);
    }
}
