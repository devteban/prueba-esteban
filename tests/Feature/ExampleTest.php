<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the home page redirects to dashboard.
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');
        $response->assertRedirect(route('dashboard'));
    }

    /**
     * Test that dashboard is accessible for authenticated users.
     */
    public function test_dashboard_is_accessible_for_authenticated_users()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }
}
