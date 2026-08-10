<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'applicant_id', 'department_id', 'job_position_id',
        'employee_id', 'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'gender', 'address', 'city', 'state', 'country',
        'nationality', 'sss_no', 'philhealth_no', 'pagibig_no', 'tin_no',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'bank_name', 'bank_account_no', 'hire_date', 'regularization_date',
        'employment_status', 'photo_path', 'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'regularization_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
