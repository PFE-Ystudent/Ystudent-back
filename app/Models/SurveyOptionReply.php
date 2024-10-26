<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyOptionReply extends Model
{
    use HasFactory;

    public function surveyOption()
    {
        return $this->belongsTo(SurveyOption::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
