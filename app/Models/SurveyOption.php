<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyOption extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function surveyOptionReplies(): HasMany
    {
        return $this->hasMany(SurveyOptionReply::class);
    }
}
