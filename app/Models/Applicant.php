<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'phone', 'address',
        'city', 'state', 'country', 'postal_code', 'date_of_birth', 'gender',
        'nationality', 'resume_path', 'photo_path', 'linkedin_url', 'portfolio_url',
        'summary', 'status', 'source',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function education()
    {
        return $this->hasMany(ApplicantEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(ApplicantExperience::class);
    }

    public function skills()
    {
        return $this->hasMany(ApplicantSkill::class);
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    public function documents()
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getTotalYearsExperienceAttribute()
    {
        $totalMonths = $this->experiences->sum(function ($exp) {
            $start = $exp->start_date ? $exp->start_date : now();
            $end = $exp->end_date ?: now();
            return $start->diffInMonths($end);
        });
        return round($totalMonths / 12, 1);
    }

    public function getSkillNamesAttribute()
    {
        return $this->skills->pluck('skill')->toArray();
    }
}
