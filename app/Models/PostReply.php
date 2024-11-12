<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class PostReply extends Model
{
    use HasFactory;

    protected $fillable = ['content'];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_archived', false);
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function postReplyUps(): HasMany
    {
        return $this->hasMany(PostReplyUp::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PostReplyFile::class);
    }

    public function getIsEditedAttribute(): bool
    {
        return $this->updated_at->getTimestamp() !== $this->created_at->getTimestamp();
    }

    public function getIsUpVotedAttribute(): bool
    {
        return $this->postReplyUps->where('user_id', Auth::user()->id)->count() === 1;
    }
}
