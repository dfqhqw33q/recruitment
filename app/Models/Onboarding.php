<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onboarding extends Model
{
    use HasFactory;

    protected $table = 'onboarding';

    protected $fillable = [
        'application_id', 'employee_id', 'assigned_to', 'start_date',
        'orientation_date', 'training_start', 'training_end', 'progress',
        'completed_checklist_ids', 'notes', 'status', 'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'orientation_date' => 'date',
        'training_start' => 'date',
        'training_end' => 'date',
        'progress' => 'integer',
        'completed_checklist_ids' => 'array',
        'completed_at' => 'datetime',
    ];

    public function calculateProgress(): int
    {
        $progress = 0;

        // 1. Employment Start Date (20%)
        if ($this->start_date && now()->startOfDay()->gte($this->start_date->startOfDay())) {
            $progress += 20;
        }

        // 2. Company Orientation (20%)
        if ($this->orientation_date && now()->startOfDay()->gte($this->orientation_date->startOfDay())) {
            $progress += 20;
        }

        // 3. Training Period (20%)
        if ($this->training_start && now()->startOfDay()->gte($this->training_start->startOfDay())) {
            $progress += 20;
        }

        // 4. Onboarding Checklist (20%)
        $totalItems = OnboardingChecklist::where('status', 'active')->count();
        if ($totalItems > 0) {
            $completedCount = is_array($this->completed_checklist_ids) ? count($this->completed_checklist_ids) : 0;
            $checklistProgress = (int) round(($completedCount / $totalItems) * 20);
            $progress += min(20, $checklistProgress);
        } else {
            $progress += 20;
        }

        // 5. Onboarding Completion (20%)
        if ($this->status === 'completed') {
            $progress += 20;
        }

        return min(100, $progress);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
