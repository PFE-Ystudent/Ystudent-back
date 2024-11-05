<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReplyFile extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'filepath'];

    public function postReply(): BelongsTo
    {
        return $this->belongsTo(PostReply::class);
    }
}