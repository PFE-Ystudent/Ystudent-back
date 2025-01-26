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

    public function getIsEditedAttribute(): bool
    {
        return $this->updated_at->getTimestamp() !== $this->created_at->getTimestamp();
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

    public function scopeFiltered(Builder $query, $validated): void
    {
        if (isset($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('title', 'like', '%' . $validated['search'] . '%')
                    ->orWhere('content', 'like', '%' . $validated['search'] . '%');
            });
        }
        if (isset($validated['author'])) {
            $query->whereHas('author', function ($q) use ($validated) {
                $q->where('username', 'like', '%' . $validated['author'] . '%');
            });
        }
        if (isset($validated['categories'])) {
            $query->whereHas('categories', function ($q) use ($validated) {
                $q->whereIn('category_id', $validated['categories']);
            });
        }
    }

    public function loadDetails(): void
    {
        $this->load(self::getDetailsRelations())
            ->loadCount(['replies']);
    }
}
