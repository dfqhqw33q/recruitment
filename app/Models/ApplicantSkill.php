<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantSkill extends Model
{
    use HasFactory;

    protected $table = 'applicant_skills';

    protected $fillable = [
        'applicant_id', 'skill', 'proficiency', 'years_of_experience',
    ];

    protected $casts = [
        'years_of_experience' => 'integer',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
