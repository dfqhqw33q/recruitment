<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'job_posting_id', 'prepared_by', 'offer_number',
        'salary', 'start_date', 'employment_type', 'terms', 'benefits',
        'sent_at', 'response_at', 'status', 'response_notes',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'start_date' => 'date',
        'sent_at' => 'datetime',
        'response_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}
