<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'code', 'department_id', 'description', 'responsibilities',
        'requirements', 'qualifications', 'required_skills', 'preferred_skills',
        'min_salary', 'max_salary', 'employment_type', 'status',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'preferred_skills' => 'array',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }

    public function employees()
    {
        return $this->hasMany(EmployeeProfile::class);
    }
}
