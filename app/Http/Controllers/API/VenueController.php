<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as APIResponse;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Venues', 'For managing Venue resources')]
class VenueController extends Controller
{
    #[Authenticated]
    #[ResponseFromApiResource(VenueResource::class, Venue::class, collection: true, paginate: true)]
    public function index(): AnonymousResourceCollection
    {
        $venues = Venue::paginate(25);

        return VenueResource::collection($venues);
    }

    #[Authenticated]
    #[ResponseFromApiResource(VenueResource::class, Venue::class)]
    public function show(Venue $venue): VenueResource
    {
        return new VenueResource($venue);
    }

    #[Authenticated]
    #[ResponseFromApiResource(VenueResource::class, Venue::class)]
    public function store(StoreVenueRequest $request): VenueResource
    {
        $payload = $request->validated();
        $venue = Venue::create($payload);

        return new VenueResource($venue);
    }

    #[Authenticated]
    #[APIResponse(status: 201)]
    public function destroy(int $venueId): Response
    {
        Venue::destroy($venueId);

        return response(null, 204);
    }

    #[Authenticated]
    #[ResponseFromApiResource(VenueResource::class, Venue::class)]
    public function update(Venue $venue, UpdateVenueRequest $request): VenueResource
    {
        $payload = $request->validated();
        $venue->update($payload);

        return new VenueResource($venue);
    }
}
