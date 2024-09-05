<?php

namespace Tests\Feature\Http\Controller\API;

use App\Http\Controllers\API\VenueController;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(VenueController::class)]
class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('endpointsDataProvider')]
    public function allEndpointsRequiresAuthentication(string $method, string $endpoint): void
    {
        $response = $this->json($method, $endpoint);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public static function endpointsDataProvider(): array
    {
        return [
            ['GET', '/api/events'],
            ['GET', '/api/events/1'],
            ['POST', '/api/events'],
            ['DELETE', '/api/events/1'],
            ['PATCH', '/api/events/1'],
        ];
    }

    #[Test]
    public function canListAllEvents(): void
    {
        $this->actingAs(User::factory()->create());

        Event::factory(5)
            ->for(Venue::factory())
            ->create();

        $response = $this->getJson('/api/events');
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    #[Test]
    public function eventsListIsPaginated(): void
    {
        $this->actingAs(User::factory()->create());

        Event::factory(5)
            ->for(Venue::factory())
            ->create();

        $response = $this->getJson('/api/events');
        $response->assertStatus(200);
        $response->assertJsonIsObject('meta');
    }

    #[Test]
    public function eventVenueIsLoaded(): void
    {
        $this->actingAs(User::factory()->create());

        Event::factory(5)
            ->for(Venue::factory())
            ->create();

        $response = $this->getJson('/api/events');
        $response->assertStatus(200);
        $response->assertJsonIsObject('data.0.venue');
        $response->assertJsonIsObject('data.1.venue');
        $response->assertJsonIsObject('data.2.venue');
    }

    #[Test]
    public function canShowOneEvent(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()
            ->for(Venue::factory())
            ->create();

        $response = $this->getJson("/api/events/$event->id");

        $response->assertStatus(200);
        $response->assertJsonIsObject('data');
        $response->assertJsonPath('data.id', $event->id);
        $response->assertJsonIsObject('data.venue');
    }

    #[Test]
    public function error404WhenEventWasNotFound(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/events/404');

        $response->assertStatus(404);
    }

    #[Test]
    public function canDeleteAnEvent(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()
            ->for(Venue::factory())
            ->create();

        $response = $this->deleteJson("/api/events/$event->id");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    #[Test]
    public function deletingEventsEndpointIsIdempotent(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->deleteJson('/api/events/123');

        $response->assertStatus(204);
    }

    #[Test]
    public function canCreateAnEvent(): void
    {
        $this->actingAs(User::factory()->create());

        $venue = Venue::factory()->create();

        $response = $this->postJson('/api/events', [
            'title' => 'Radiohead Concert',
            'description' => 'With Thom Yorke in person',
            'date' => '2025-03-01 23:00:00',
            'venue_id' => $venue->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('events', [
            'title' => 'Radiohead Concert',
            'description' => 'With Thom Yorke in person',
            'date' => '2025-03-01 23:00:00',
            'venue_id' => $venue->id,
        ]);
    }

    #[Test]
    public function cannotCreateAnEventWithoutAnExistingVenue(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/events', [
            'title' => 'Radiohead Concert',
            'description' => 'With Thom Yorke in person',
            'date' => '2025-03-01 23:00:00',
            'venue_id' => 422,
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function canUpdateAnEvent(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()
            ->for(Venue::factory())
            ->create();

        $response = $this->patchJson("/api/events/$event->id", [
            'title' => 'O Cinza Concerto',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('events', [
            'title' => 'O Cinza Concerto',
            'description' => $event->description,
            'date' => $event->date,
        ]);
    }

    #[Test]
    public function caChangeTheVenueOfAnEvent(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()
            ->for(Venue::factory())
            ->create();

        $newVenue = Venue::factory()->create();

        $response = $this->patchJson("/api/events/$event->id", [
            'title' => 'O Cinza Concerto',
            'venue_id' => $newVenue->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('events', [
            'title' => 'O Cinza Concerto',
            'description' => $event->description,
            'date' => $event->date,
            'venue_id' => $newVenue->id,
        ]);
    }

    #[Test]
    public function errorWhenTryingToUpdateNonExistentEvent(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->patchJson('/api/events/404', [
            'title' => 'Rock in Rio 2024',
        ]);

        $response->assertStatus(404);
    }
}
