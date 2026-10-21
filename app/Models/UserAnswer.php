<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswer extends Model
{
    protected $fillable = [
        'exam_session_id',
        'question_id',
        'choice_id',
        'is_doubtful',
    ];

    protected function casts(): array
    {
        return [
            'is_doubtful' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function choice(): BelongsTo
    {
        return $this->belongsTo(Choice::class);
    }

    public function scopeDoubtful(Builder $query): Builder
    {
        return $query->where('is_doubtful', true);
    }

    public function scopeAnswered(Builder $query): Builder
    {
        return $query->whereNotNull('choice_id');
    }

    public function scopeUnanswered(Builder $query): Builder
    {
        return $query->whereNull('choice_id');
    }
}
