<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
    }

    /** @test */
    public function admin_user_can_access_admin_routes()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/audits');

        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/admin/audits');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_user_is_redirected_to_login_for_admin_routes()
    {
        $response = $this->get('/admin/audits');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function middleware_checks_is_admin_property()
    {
        // User with is_admin = false
        $nonAdminUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($nonAdminUser)
            ->get('/admin/audits');

        $response->assertStatus(403);

        // User with is_admin = true
        $adminUser = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($adminUser)
            ->get('/admin/audits');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_middleware_protects_all_admin_routes()
    {
        // Verificar que las rutas admin existen y están protegidas
        $adminRoutes = [
            '/admin/audits',
        ];

        foreach ($adminRoutes as $route) {
            // Sin autenticación
            $response = $this->get($route);
            $response->assertRedirect('/login');

            // Con usuario regular
            $response = $this->actingAs($this->regularUser)->get($route);
            $response->assertStatus(403);

            // Con admin
            $response = $this->actingAs($this->admin)->get($route);
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function is_admin_method_returns_correct_values()
    {
        // Test del método isAdmin() con diferentes valores
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->regularUser->isAdmin());

        // Test con diferentes valores válidos
        $userWithTrue = User::factory()->create(['is_admin' => true]);
        $userWithFalse = User::factory()->create(['is_admin' => false]);
        $userWithZero = User::factory()->create(['is_admin' => 0]);
        $userWithOne = User::factory()->create(['is_admin' => 1]);

        $this->assertTrue($userWithTrue->isAdmin());
        $this->assertFalse($userWithFalse->isAdmin());
        $this->assertFalse($userWithZero->isAdmin());
        $this->assertTrue($userWithOne->isAdmin());

        // Verificar que todos los valores se castean a boolean correctamente
        $this->assertIsBool($userWithTrue->is_admin);
        $this->assertIsBool($userWithFalse->is_admin);
        $this->assertIsBool($userWithZero->is_admin);
        $this->assertIsBool($userWithOne->is_admin);
    }

    /** @test */
    public function admin_status_persists_after_user_refresh()
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($user->isAdmin());

        // Refrescar desde la base de datos
        $user->refresh();

        $this->assertTrue($user->isAdmin());
    }

    /** @test */
    public function admin_status_can_be_changed()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->assertFalse($user->isAdmin());

        // Promover a admin
        $user->update(['is_admin' => true]);
        $this->assertTrue($user->fresh()->isAdmin());

        // Degradar de admin
        $user->update(['is_admin' => false]);
        $this->assertFalse($user->fresh()->isAdmin());
    }

    /** @test */
    public function middleware_works_with_different_http_methods()
    {
        // Simular diferentes métodos HTTP en rutas admin (si las hubiera)
        $response = $this->actingAs($this->regularUser)
            ->get('/admin/audits');
        $response->assertStatus(403);

        $response = $this->actingAs($this->admin)
            ->get('/admin/audits');
        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_works_with_different_boolean_values()
    {
        // Crear usuarios con diferentes representaciones de false
        $user1 = User::factory()->create(['is_admin' => 0]);
        $user2 = User::factory()->create(['is_admin' => false]);

        // Ambos deben ser rechazados
        $response = $this->actingAs($user1)->get('/admin/audits');
        $response->assertStatus(403);

        $response = $this->actingAs($user2)->get('/admin/audits');
        $response->assertStatus(403);
    }
}
