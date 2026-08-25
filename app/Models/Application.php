<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_code', 'applicant_id', 'job_posting_id', 'reviewed_by', 'status', 'cover_letter',
        'custom_resume_path', 'custom_notes', 'screening_answers', 'is_knocked_out', 'knockout_reason',
        'applied_at', 'screening_date', 'reviewed_at', 'screening_notes', 'rejection_reason',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_code)) {
                $model->reference_code = 'APP-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });

        static::updated(function ($application) {
            if ($application->isDirty('status') && $application->status === 'hired') {
                static::handleHiredApplication($application);
            }
        });

        static::created(function ($application) {
            if ($application->status === 'hired') {
                static::handleHiredApplication($application);
            }
        });
    }

    public static function handleHiredApplication(Application $application)
    {
        $applicant = $application->applicant;
        if (!$applicant) return;

        // 1. Update applicant record — keep as 'active' (hired is an application-level status, not applicant)
        $applicant->update(['status' => 'active']);

        // 2. Convert user role to 'Employee'
        $user = $applicant->user;
        if ($user) {
            $user->syncRoles(['Employee']);
        }

        // 3. Create or ensure EmployeeProfile exists
        $posting = $application->jobPosting;
        EmployeeProfile::firstOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'user_id' => $user?->id,
                'department_id' => $posting?->department_id,
                'job_position_id' => $posting?->job_position_id,
                'employee_id' => 'EMP-' . date('Y') . '-' . str_pad($applicant->id, 4, '0', STR_PAD_LEFT),
                'first_name' => $applicant->first_name,
                'last_name' => $applicant->last_name,
                'email' => $applicant->email,
                'phone' => $applicant->phone,
                'date_of_birth' => $applicant->date_of_birth,
                'gender' => $applicant->gender,
                'address' => $applicant->address,
                'city' => $applicant->city,
                'state' => $applicant->state,
                'country' => $applicant->country,
                'nationality' => $applicant->nationality,
                'hire_date' => now(),
                'employment_status' => 'probationary',
                'status' => 'active',
            ]
        );
    }

    protected $casts = [
        'applied_at' => 'datetime',
        'screening_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'screening_answers' => 'array',
        'is_knocked_out' => 'boolean',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function aiRecommendation()
    {
        return $this->hasOne(AiRecommendation::class);
    }

    public function offerLetter()
    {
        return $this->hasOne(OfferLetter::class);
    }

    public function onboarding()
    {
        return $this->hasOne(Onboarding::class);
    }

    public function documents()
    {
        return $this->hasMany(UploadedDocument::class);
    }

    public function getLatestInterviewAttribute()
    {
        return $this->interviews()->latest('scheduled_at')->first();
    }

    public function getStatusTimelineAttribute()
    {
        return [
            'submitted' => $this->applied_at,
            'under_review' => $this->reviewed_at,
            'screening' => $this->screening_date,
        ];
    }

    public function getResumePathAttribute(): ?string
    {
        if ($this->custom_resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->custom_resume_path)) {
            return $this->custom_resume_path;
        }

        if ($this->applicant?->resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->applicant->resume_path)) {
            return $this->applicant->resume_path;
        }

        $docResume = $this->documents->where('document_type', 'resume')->first()
            ?? $this->applicant?->documents->where('document_type', 'resume')->first();
        if ($docResume && \Illuminate\Support\Facades\Storage::disk('public')->exists($docResume->file_path)) {
            return $docResume->file_path;
        }

        return $this->custom_resume_path ?: $this->applicant?->resume_path;
    }

    public function getHasResumeAttribute(): bool
    {
        $path = $this->resume_path;
        return !empty($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    }

    public function getResumeExtensionAttribute(): string
    {
        $path = $this->resume_path;
        return $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : 'pdf';
    }

    public function getResumeFileNameAttribute(): string
    {
        $candidateName = $this->applicant ? $this->applicant->full_name : 'Applicant';
        $ref = $this->reference_code ?: 'APP';
        $ext = $this->resume_extension ?: 'pdf';
        return "Resume_{$candidateName}_{$ref}.{$ext}";
    }

    public function getResumeFileSizeAttribute(): ?string
    {
        $path = $this->resume_path;
        if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            $bytes = \Illuminate\Support\Facades\Storage::disk('public')->size($path);
            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 1) . ' KB';
            }
            return $bytes . ' B';
        }
        return null;
    }

    public function getResumeTypeLabelAttribute(): string
    {
        if ($this->custom_resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->custom_resume_path)) {
            return 'Position-Specific Custom CV';
        }
        if ($this->applicant?->resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->applicant->resume_path)) {
            return 'Candidate Profile Master Resume';
        }
        return 'Submitted Resume Document';
    }
}
