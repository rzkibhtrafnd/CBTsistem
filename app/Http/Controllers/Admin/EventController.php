<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventIndexRequest;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Services\EventService;
use App\Support\PaginationOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(private readonly EventService $eventService) {}

    public function index(EventIndexRequest $request): Response
    {
        $filters = $request->validated();

        return Inertia::render('Admin/Events/Index', [
            'events' => $this->eventService->getFilteredEvents($filters, PaginationOptions::perPage($request)),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Events/Create', $this->eventService->getEventOptions());
    }

    public function store(EventRequest $request): RedirectResponse
    {
        $this->eventService->createEvent($request->validated());

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dibuat dan peserta telah ditugaskan.');
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Admin/Events/Edit', [
            ...$this->eventService->getEventOptions(),
            'event' => $this->eventService->getEventForEditing($event),
        ]);
    }

    public function show(Event $event): Response
    {
        return Inertia::render('Admin/Events/Show', [
            'event' => $this->eventService->getEventDetails($event),
        ]);
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $this->eventService->updateEvent($event, $request->validated());

        return redirect()->route('admin.events.index')->with('success', 'Event dan penugasannya berhasil diperbarui.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        try {
            $this->eventService->deleteEvent($event);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'Event tidak dapat dihapus karena masih memiliki riwayat ujian.';

            return redirect()->route('admin.events.index')->with('error', $message);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }
}
