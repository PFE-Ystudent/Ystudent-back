<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;


class Conversation extends Model
{

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function scopeIsVisible(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where(function ($q) {
                $q->where('requester_id', Auth::user()->id)
                ->where('is_closed_requester', false);
            })->orWhere(function ($q) {
                $q->where('user_id', Auth::user()->id)
                ->where('is_closed_user', false);
            });
        });
    }

    public static function getConversation(int $userId)
    {
        return Conversation::query()
                ->where(function ($q) use ($userId) {
                    $q->where('requester_id', Auth::user()->id)
                        ->where('user_id', $userId);
                })->orWhere(function ($q) use ($userId) {
                    $q->where('user_id', Auth::user()->id)
                    ->where('requester_id', $userId);
                })->first();
    }
}
