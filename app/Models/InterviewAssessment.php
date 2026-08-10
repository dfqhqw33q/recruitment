<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id', 'assessor_id', 'communication_score', 'technical_score',
        'experience_score', 'cultural_fit_score', 'overall_score',
        'strengths', 'weaknesses', 'comments', 'status',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function getAverageScoreAttribute()
    {
        return round(($this->communication_score + $this->technical_score
            + $this->experience_score + $this->cultural_fit_score) / 4, 2);
    }
}
