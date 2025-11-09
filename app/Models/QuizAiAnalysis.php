<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAiAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'status',
        'summary',
        'recommendations',
        'quantitative_insights',
        'qualitative_themes',
        'raw_response',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'quantitative_insights' => 'array',
        'qualitative_themes' => 'array',
        'raw_response' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
