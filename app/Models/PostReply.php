<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostReply extends Model
{
    use HasFactory;

    protected $fillable = ['content'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PostReplyFile::class);
    }
}
