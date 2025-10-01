<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_factory_creates_valid_user()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
        $this->assertNotEmpty($user->password);
        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
        $this->assertTrue(filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false);
        $this->assertIsBool($user->is_admin);
        $this->assertFalse($user->is_admin); // Por defecto debería ser false
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function user_factory_can_override_attributes()
    {
        $user = User::factory()->create([
            'name' => 'Custom Name',
            'email' => 'custom@example.com',
            'is_admin' => true,
        ]);

        $this->assertEquals('Custom Name', $user->name);
        $this->assertEquals('custom@example.com', $user->email);
        $this->assertTrue($user->is_admin);
    }

    /** @test */
    public function user_factory_creates_multiple_unique_users()
    {
        $users = User::factory()->count(5)->create();

        $this->assertCount(5, $users);

        $emails = $users->pluck('email')->toArray();
        $uniqueEmails = array_unique($emails);

        // Todos los emails deben ser únicos
        $this->assertEquals(count($emails), count($uniqueEmails));
    }

    /** @test */
    public function task_factory_creates_valid_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertNotEmpty($task->title);
        $this->assertIsString($task->title);
        $this->assertIsInt($task->user_id);
        $this->assertEquals($user->id, $task->user_id);
        $this->assertContains($task->status, ['pending', 'in_progress', 'completed']);
        $this->assertIsInt($task->order);
        $this->assertGreaterThanOrEqual(0, $task->order);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $user->id,
            'title' => $task->title,
        ]);
    }

    /** @test */
    public function task_factory_can_override_attributes()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Custom Task Title',
            'description' => 'Custom Description',
            'status' => 'completed',
            'order' => 99,
        ]);

        $this->assertEquals('Custom Task Title', $task->title);
        $this->assertEquals('Custom Description', $task->description);
        $this->assertEquals('completed', $task->status);
        $this->assertEquals(99, $task->order);
        $this->assertEquals($user->id, $task->user_id);
    }

    /** @test */
    public function task_factory_creates_multiple_tasks()
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(10)->create(['user_id' => $user->id]);

        $this->assertCount(10, $tasks);

        foreach ($tasks as $task) {
            $this->assertEquals($user->id, $task->user_id);
            $this->assertNotEmpty($task->title);
            $this->assertContains($task->status, ['pending', 'in_progress', 'completed']);
        }
    }

    /** @test */
    public function task_factory_can_create_tasks_without_user_parameter()
    {
        $task = Task::factory()->create();

        $this->assertInstanceOf(Task::class, $task);
        $this->assertIsInt($task->user_id);

        // Should create a user automatically
        $this->assertDatabaseHas('users', ['id' => $task->user_id]);
    }

    /** @test */
    public function task_factory_generates_realistic_data()
    {
        $task = Task::factory()->create();

        // El título debe tener una longitud razonable
        $this->assertLessThanOrEqual(255, strlen($task->title));
        $this->assertGreaterThan(0, strlen($task->title));

        // La descripción, si existe, debe tener una longitud razonable
        if ($task->description) {
            $this->assertLessThanOrEqual(1000, strlen($task->description));
            $this->assertGreaterThan(0, strlen($task->description));
        }
    }

    /** @test */
    public function user_factory_generates_realistic_data()
    {
        $user = User::factory()->create();

        // El nombre debe tener una longitud razonable
        $this->assertLessThanOrEqual(255, strlen($user->name));
        $this->assertGreaterThan(0, strlen($user->name));

        // El email debe tener una longitud razonable y formato válido
        $this->assertLessThanOrEqual(255, strlen($user->email));
        $this->assertGreaterThan(0, strlen($user->email));
        $this->assertTrue(filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false);

        // La contraseña debe estar hasheada
        $this->assertNotEmpty($user->password);
        $this->assertNotEquals('password', $user->password); // No debe ser texto plano
    }

    /** @test */
    public function factories_work_together_for_related_models()
    {
        $user = User::factory()
            ->has(Task::factory()->count(3), 'tasks')
            ->create();

        $this->assertCount(3, $user->tasks);

        foreach ($user->tasks as $task) {
            $this->assertEquals($user->id, $task->user_id);
        }
    }

    /** @test */
    public function task_factory_can_create_tasks_with_different_statuses()
    {
        $user = User::factory()->create();
        $statuses = ['pending', 'in_progress', 'completed'];

        foreach ($statuses as $status) {
            $task = Task::factory()->create([
                'user_id' => $user->id,
                'status' => $status
            ]);

            $this->assertEquals($status, $task->status);
        }
    }

    /** @test */
    public function user_factory_can_create_admin_users()
    {
        $adminUser = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($adminUser->is_admin);
        $this->assertTrue($adminUser->isAdmin());

        $this->assertFalse($regularUser->is_admin);
        $this->assertFalse($regularUser->isAdmin());
    }
}
