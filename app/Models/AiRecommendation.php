<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'job_posting_id', 'match_score', 'skills_match_percentage',
        'confidence_score', 'recommendation', 'missing_skills', 'strengths',
        'weaknesses', 'qualification_gaps', 'explanation', 'summary',
        'score_breakdown', 'rank', 'status',
    ];

    protected $casts = [
        'missing_skills' => 'array',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'qualification_gaps' => 'array',
        'score_breakdown' => 'array',
        'confidence_score' => 'decimal:2',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function getRecommendationLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->recommendation));
    }
}
