<?php

namespace App\Models;

use App\Enums\ExamSessionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'test_id',
        'start_time',
        'end_time',
        'status',
        'total_score',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'status' => ExamSessionStatus::class,
            'total_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function exam(): BelongsTo
    {
        return $this->test();
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'exam_session_id');
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('status', ExamSessionStatus::ONGOING);
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', ExamSessionStatus::FINISHED);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('test', function (Builder $testQuery) use ($search) {
                        $testQuery->where('title', 'like', "%{$search}%");
                    })->orWhereHas('event', function (Builder $eventQuery) use ($search) {
                        $eventQuery->where('title', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filters['test_id'] ?? null, fn (Builder $query, int|string $testId) => $query->where('test_id', $testId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }
}
