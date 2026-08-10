<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description', 'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function jobPositions()
    {
        return $this->hasMany(JobPosition::class);
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
