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
class VenueControllerTest extends TestCase
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
            ['GET', '/api/venues'],
            ['GET', '/api/venues/1'],
            ['POST', '/api/venues'],
            ['DELETE', '/api/venues/1'],
            ['PATCH', '/api/venues/1'],
        ];
    }

    #[Test]
    public function canListAllVenues(): void
    {
        $this->actingAs(User::factory()->create());

        Venue::factory(5)->create();

        $response = $this->getJson('/api/venues');
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    #[Test]
    public function listIsPaginated(): void
    {
        $this->actingAs(User::factory()->create());

        Venue::factory(5)->create();

        $response = $this->getJson('/api/venues');
        $response->assertStatus(200);
        $response->assertJsonIsObject('meta');
    }

    #[Test]
    public function canSeeOneVenue(): void
    {
        $this->actingAs(User::factory()->create());

        $venue = Venue::factory()->create();

        $response = $this->getJson("/api/venues/$venue->id");

        $response->assertStatus(200);
        $response->assertJsonIsObject('data');
        $response->assertJsonPath('data.id', $venue->id);
        $response->assertJsonPath('data.name', $venue->name);
        $response->assertJsonPath('data.capacity', $venue->capacity);
    }

    #[Test]
    public function error404WhenVenueWasNotFound(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/venues/999');

        $response->assertNotFound();
    }

    #[Test]
    public function canDeleteVenue(): void
    {
        $this->actingAs(User::factory()->create());

        $venue = Venue::factory()->create();

        $response = $this->deleteJson("/api/venues/$venue->id");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('venues', ['id' => $venue->id]);
    }

    #[Test]
    public function deletingVenuesEndpointIsIdempotent(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->deleteJson('/api/venues/123');

        $response->assertStatus(204);
    }

    #[Test]
    public function canCreateAVenue(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson('/api/venues', [
            'name' => 'Venue Name',
            'capacity' => 1000,
            'address' => 'Nowhere Street',
            'city' => 'Nowhere Town',
            'state' => 'NS',
            'country' => 'NZ',
            'postal_code' => '10000',
            'website' => 'https://venuename.com',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('venues', [
            'name' => 'Venue Name',
            'capacity' => 1000,
            'address' => 'Nowhere Street',
            'city' => 'Nowhere Town',
            'state' => 'NS',
            'country' => 'NZ',
            'postal_code' => '10000',
            'website' => 'https://venuename.com',
        ]);
    }

    #[Test]
    public function cannotCreateAVenueWithInvalidData(): void
    {
        $this->actingAs(User::factory()->create());

        // missing required fields
        $response = $this->postJson('/api/venues', [
            'name' => 'Venue Name',
            'address' => 'Nowhere Street',
            'city' => 'Nowhere Town',
            'country' => 'NZ',
            'postal_code' => '10000',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function canUpdateAVenue(): void
    {
        $this->actingAs(User::factory()->create());

        $venue = Venue::factory()->create();

        $response = $this->patchJson("/api/venues/$venue->id", [
            'name' => 'New Name',
            'capacity' => 2000,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('venues', [
            'name' => 'New Name',
            'capacity' => 2000,
            'city' => $venue->city,
            'state' => $venue->state,
            'country' => $venue->country,
            'postal_code' => $venue->postal_code,
            'website' => $venue->website,
        ]);
    }

    #[Test]
    public function errorWhenTryingToUpdateNonExistentVenue(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->patchJson('/api/venues/404', [
            'name' => 'New Name',
            'capacity' => 2000,
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function errorWhenTryingToUpdateVenueWithBadData(): void
    {
        $this->actingAs(User::factory()->create());

        $venue = Venue::factory()->create();

        // capacity cannot be null
        $response = $this->patchJson("/api/venues/$venue->id", [
            'name' => 'New Name',
            'capacity' => -2000,
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function deletingAVenueRemovesAllAssociatedEvents(): void
    {
        $this->actingAs(User::factory()->create());

        $venue = Venue::factory()
            ->has(Event::factory()->count(3))
            ->create();

        $response = $this->deleteJson("/api/venues/$venue->id");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('venues', ['id' => $venue->id]);
        $this->assertDatabaseMissing('events', ['venue_id' => $venue->id]);
    }
}
