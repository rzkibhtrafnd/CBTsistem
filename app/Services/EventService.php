<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Test;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventService
{
    public function getFilteredEvents(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        return Event::query()
            ->withCount(['tests', 'participants', 'sessions'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getEventOptions(): array
    {
        return [
            'tests' => Test::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title', 'duration_minutes', 'is_active']),
            'participants' => User::students()
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ];
    }

    public function getEventDetails(Event $event): Event
    {
        return $event
            ->load([
                'tests' => fn ($query) => $query->withCount('questions')->orderBy('title'),
                'participants:id,name,email',
            ])
            ->loadCount(['tests', 'participants', 'sessions']);
    }

    public function getEventForEditing(Event $event): Event
    {
        return $event
            ->load([
                'tests:id,title,duration_minutes,is_active',
                'participants:id,name,email',
            ])
            ->loadCount(['tests', 'participants', 'sessions']);
    }

    public function createEvent(array $data): Event
    {
        return $this->saveEvent(new Event, $data);
    }

    public function updateEvent(Event $event, array $data): Event
    {
        return $this->saveEvent($event, $data);
    }

    public function deleteEvent(Event $event): void
    {
        if ($event->sessions()->exists()) {
            throw ValidationException::withMessages([
                'event' => 'Event yang sudah memiliki sesi ujian tidak dapat dihapus.',
            ]);
        }

        $event->delete();
    }

    private function saveEvent(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($data, $event) {
            $testIds = $data['test_ids'] ?? [];
            $participantIds = $data['participant_ids'] ?? [];
            unset($data['test_ids'], $data['participant_ids']);

            if ($event->exists) {
                $event = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();

                if ($event->sessions()->exists()) {
                    $hasScheduleChanged =
                        $event->start_time?->toDateTimeString() !== Carbon::parse($data['start_time'])->toDateTimeString() ||
                        $event->end_time?->toDateTimeString() !== Carbon::parse($data['end_time'])->toDateTimeString();

                    if ($hasScheduleChanged) {
                        throw ValidationException::withMessages([
                            'start_time' => 'Jadwal event yang sudah memiliki sesi peserta tidak dapat diubah.',
                        ]);
                    }
                }

                $removedTestIds = $event->tests()->whereNotIn('tests.id', $testIds)->pluck('tests.id');
                if ($removedTestIds->isNotEmpty() && $event->sessions()->whereIn('test_id', $removedTestIds)->exists()) {
                    throw ValidationException::withMessages([
                        'test_ids' => 'Test yang sudah dikerjakan peserta tidak dapat dilepas dari event.',
                    ]);
                }

                $removedParticipantIds = $event->participants()->whereNotIn('users.id', $participantIds)->pluck('users.id');
                if ($removedParticipantIds->isNotEmpty() && $event->sessions()->whereIn('user_id', $removedParticipantIds)->exists()) {
                    throw ValidationException::withMessages([
                        'participant_ids' => 'Peserta yang sudah memiliki sesi tidak dapat dilepas dari event.',
                    ]);
                }
            }

            $event->fill($data);
            $event->save();

            $event->tests()->sync($testIds);
            $event->participants()->sync($participantIds);

            return $event;
        });
    }
}