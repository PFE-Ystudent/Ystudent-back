<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostFile extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'filepath'];

    public function getUrlAttribute(): string
    {
        return url('storage/' . $this->filepath);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
