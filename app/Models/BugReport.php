<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugReport extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'is_processed', 'is_done', 'is_archived', 'filepath'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reportingCategory(): BelongsTo
    {
        return $this->belongsTo(ReportingCategory::class, 'reporting_category_id');
    }
}
