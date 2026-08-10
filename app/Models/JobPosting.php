<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_position_id', 'department_id', 'posted_by', 'title', 'slug',
        'summary', 'description', 'vacancies_count', 'required_skills',
        'preferred_skills', 'requirements', 'qualifications', 'screening_questions', 'employment_type',
        'location', 'salary_range', 'source', 'posted_date', 'closing_date',
        'estimated_cost', 'status',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'preferred_skills' => 'array',
        'screening_questions' => 'array',
        'vacancies_count' => 'integer',
        'posted_date' => 'date',
        'closing_date' => 'date',
        'estimated_cost' => 'decimal:2',
    ];

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function aiRecommendations()
    {
        return $this->hasMany(AiRecommendation::class);
    }

    public function offerLetters()
    {
        return $this->hasMany(OfferLetter::class);
    }

    public function getApplicationCountAttribute()
    {
        return $this->applications()->count();
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'published'
            && (!$this->closing_date || $this->closing_date->gte(now()));
    }
}
