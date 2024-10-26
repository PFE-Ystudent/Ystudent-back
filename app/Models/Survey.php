<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = ['question'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function surveyOptions()
    {
        return $this->hasMany(SurveyOption::class);
    }
}
