<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'about'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'role_id' => 3
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function postReplies(): HasMany
    {
        return $this->hasMany(PostReply::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_id')
            ->orWhere((new Conversation())->getTable() . '.requester_id', $this->id);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? url('storage/' . $this->avatar) : null;
    }

    public function getRelationWith($user)
    {
        return UserRelation::query()
            ->where(function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->where('user_id', $this->id)
                        ->where('requester_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->where('requester_id', $this->id);
                });
            })
            ->first() ?? null;
    }
}
