<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $user = new User();
        $fillable = ['name', 'email', 'password', 'is_admin'];

        $this->assertEquals($fillable, $user->getFillable());
    }

    /** @test */
    public function it_has_hidden_attributes()
    {
        $user = new User();
        $hidden = ['password', 'remember_token'];

        $this->assertEquals($hidden, $user->getHidden());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $user = new User();
        $expectedCasts = [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
        ];

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $user->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function it_has_many_tasks()
    {
        $user = new User();
        $this->assertInstanceOf(HasMany::class, $user->tasks());
    }

    /** @test */
    public function is_admin_returns_correct_boolean_value()
    {
        $adminUser = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($regularUser->isAdmin());
    }

    /** @test */
    public function is_admin_handles_different_falsy_values()
    {
        // Test con diferentes valores falsy que son válidos
        $user1 = User::factory()->create(['is_admin' => 0]);
        $this->assertFalse($user1->isAdmin());

        $user2 = User::factory()->create(['is_admin' => false]);
        $this->assertFalse($user2->isAdmin());

        // Verificar que el cast funciona correctamente
        $this->assertIsBool($user1->is_admin);
        $this->assertIsBool($user2->is_admin);
    }

    /** @test */
    public function user_can_have_multiple_tasks()
    {
        $user = User::factory()->create();

        $task1 = Task::factory()->create(['user_id' => $user->id]);
        $task2 = Task::factory()->create(['user_id' => $user->id]);
        $task3 = Task::factory()->create(['user_id' => $user->id]);

        $this->assertEquals(3, $user->tasks->count());
        $this->assertTrue($user->tasks->contains($task1));
        $this->assertTrue($user->tasks->contains($task2));
        $this->assertTrue($user->tasks->contains($task3));
    }

    /** @test */
    public function user_tasks_relationship_is_isolated_per_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $task1 = Task::factory()->create(['user_id' => $user1->id]);
        $task2 = Task::factory()->create(['user_id' => $user2->id]);

        $this->assertEquals(1, $user1->tasks->count());
        $this->assertEquals(1, $user2->tasks->count());

        $this->assertTrue($user1->tasks->contains($task1));
        $this->assertFalse($user1->tasks->contains($task2));

        $this->assertTrue($user2->tasks->contains($task2));
        $this->assertFalse($user2->tasks->contains($task1));
    }

    /** @test */
    public function user_can_be_created_with_all_attributes()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->isAdmin());
    }

    /** @test */
    public function user_can_be_created_without_is_admin_flag()
    {
        $user = User::factory()->create();

        // Por defecto debería ser false
        $this->assertFalse($user->is_admin);
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function is_admin_cast_works_with_different_input_types()
    {
        // Test con 1/0
        $user1 = User::factory()->create(['is_admin' => 1]);
        $this->assertTrue($user1->isAdmin());

        $user2 = User::factory()->create(['is_admin' => 0]);
        $this->assertFalse($user2->isAdmin());

        // Test con string - Laravel convierte cualquier string no vacío a true
        $user3 = User::factory()->create(['is_admin' => '1']);
        $this->assertTrue($user3->isAdmin());

        // Solo '0' y string vacío se convierten a false
        $user4 = User::factory()->create(['is_admin' => '0']);
        $this->assertFalse($user4->isAdmin());

        // Verificar que los valores se castean a boolean correctamente
        $this->assertIsBool($user1->is_admin);
        $this->assertIsBool($user2->is_admin);
        $this->assertIsBool($user3->is_admin);
        $this->assertIsBool($user4->is_admin);
    }

    /** @test */
    public function password_is_hidden_in_array_output()
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password')
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
    }

    /** @test */
    public function user_uses_required_traits()
    {
        $user = new User();
        $traits = class_uses_recursive($user);

        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $traits);
        $this->assertContains('Illuminate\Notifications\Notifiable', $traits);
        $this->assertContains('Laravel\Sanctum\HasApiTokens', $traits);
    }

    /** @test */
    public function user_extends_authenticatable()
    {
        $user = new User();
        $this->assertInstanceOf('Illuminate\Foundation\Auth\User', $user);
    }

    /** @test */
    public function email_verified_at_is_cast_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => '2023-01-01 12:00:00'
        ]);

        $this->assertInstanceOf('Illuminate\Support\Carbon', $user->email_verified_at);
    }

    /** @test */
    public function user_can_have_tasks_with_different_statuses()
    {
        $user = User::factory()->create();

        $pendingTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $inProgressTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress'
        ]);

        $completedTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed'
        ]);

        $this->assertEquals(3, $user->tasks->count());

        $statuses = $user->tasks->pluck('status')->toArray();
        $this->assertContains('pending', $statuses);
        $this->assertContains('in_progress', $statuses);
        $this->assertContains('completed', $statuses);
    }
}
