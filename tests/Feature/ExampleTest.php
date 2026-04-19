<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_gets_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
