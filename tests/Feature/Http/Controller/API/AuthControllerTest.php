<?php

namespace Tests\Feature\Http\Controller\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(AuthControllerTest::class)]
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function testogin(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        dd($response->json());
    }
}
