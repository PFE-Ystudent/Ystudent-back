<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyOptionReply extends Model
{
    use HasFactory;

    public function surveyOption(): BelongsTo
    {
        return $this->belongsTo(SurveyOption::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
