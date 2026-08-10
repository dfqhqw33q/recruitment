<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'interviewer_id', 'scheduled_by', 'scheduled_at',
        'location', 'meeting_link', 'type', 'round', 'duration_minutes',
        'notes', 'status', 'reminder_sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'duration_minutes' => 'integer',
        'round' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function assessment()
    {
        return $this->hasOne(InterviewAssessment::class);
    }
}
