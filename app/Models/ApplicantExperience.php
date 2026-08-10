<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantExperience extends Model
{
    use HasFactory;

    protected $table = 'applicant_experience';

    protected $fillable = [
        'applicant_id', 'company', 'job_title', 'location', 'start_date',
        'end_date', 'is_current', 'description', 'skills_used',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'skills_used' => 'array',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
