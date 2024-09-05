<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as APIResponse;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Events', 'For managing Event resources')]
class EventController extends Controller
{
    #[Authenticated]
    #[ResponseFromApiResource(EventResource::class, Event::class, collection: true, with: ['venue'], paginate: true)]
    public function index(): AnonymousResourceCollection
    {
        $events = Event::with('venue')->paginate(25);

        return EventResource::collection($events);
    }

    #[Authenticated]
    #[ResponseFromApiResource(EventResource::class, Event::class, with: ['venue'])]
    public function show(int $eventId): EventResource
    {
        $event = Event::with('venue')->findOrFail($eventId);

        return new EventResource($event);
    }

    #[Authenticated]
    #[APIResponse(status: 201)]
    public function destroy(int $eventId): Response
    {
        Event::destroy($eventId);

        return response(null, 204);
    }

    #[Authenticated]
    #[ResponseFromApiResource(EventResource::class, Event::class, with: ['venue'])]
    public function store(StoreEventRequest $request): EventResource
    {
        $payload = $request->validated();

        $venue = Venue::findOrFail($payload['venue_id']);

        $event = new Event($payload);
        $event->venue()->associate($venue);
        $event->save();

        return new EventResource($event);
    }

    #[Authenticated]
    #[ResponseFromApiResource(EventResource::class, Event::class, with: ['venue'])]
    public function update(Event $event, UpdateEventRequest $request): EventResource
    {
        $payload = $request->validated();
        $newVenue = isset($payload['venue_id']);

        if ($newVenue) {
            $venueId = $payload['venue_id'];
            $venue = Venue::findOrFail($venueId);

            $event->venue()->associate($venue);
        }

        $event->update($payload);

        return new EventResource($event);
    }
}
