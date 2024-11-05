<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = ['question'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function surveyOptions(): HasMany
    {
        return $this->hasMany(SurveyOption::class);
    }
}
