<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiPipelineInsight extends Model
{
    use HasFactory;

protected $fillable = [
        'title', 'category', 'priority', 'summary', 'evidence', 'impact',
        'recommendation', 'explanation', 'content', 'icon', 'sort_order',
        'data_signature', 'generated_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'generated_at' => 'datetime',
    ];
}
