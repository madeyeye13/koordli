<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class FeedbackSubmission extends Model
{
    protected $fillable = [
        'name', 'experience', 'navigation_rating', 'clarity_rating',
        'most_useful', 'had_confusion', 'confusion_details', 'improvement',
        'likelihood', 'overall_rating', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'had_confusion' => 'boolean',
        'most_useful' => 'array',
    ];
}
