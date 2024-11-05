<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content'];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_archived', false);
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PostReply::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PostFile::class);
    }

    public static function getDetailsRelations (): array
    {
        return [
            'files',
            'author',
            'categories',
            'surveys',
            'surveys.surveyOptions' => function ($q) {
                $q->withCount('surveyOptionReplies')
                    ->withExists(['surveyOptionReplies' => function ($q) {
                    $q->where('user_id', Auth::user()->id);
                }]);
            },
        ];
    }

    public function scopeWithDetails(Builder $query): void
    {
        $query->with(self::getDetailsRelations())
        ->withCount(['replies']);
    }

    public function loadDetails(): void
    {
        $this->load(self::getDetailsRelations())
        ->loadCount(['replies']);
    }
}
