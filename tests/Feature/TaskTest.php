<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_admin' => false]);
        $this->otherUser = User::factory()->create(['is_admin' => false]);
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /** @test */
    public function user_can_create_task()
    {
        $this->actingAs($this->user);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending'
        ];

        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => $taskData['title'],
            'description' => $taskData['description'],
            'status' => $taskData['status'],
            'order' => 1
        ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'title' => 'Test Task',
            'status' => 'pending'
        ]);

        $this->assertEquals($this->user->id, $task->user_id);
    }

    /** @test */
    public function user_can_only_see_their_own_tasks()
    {
        $task1 = Task::factory()->create(['user_id' => $this->user->id]);
        $task2 = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $user1Tasks = Task::where('user_id', $this->user->id)->get();
        $user2Tasks = Task::where('user_id', $this->otherUser->id)->get();

        $this->assertTrue($user1Tasks->contains($task1));
        $this->assertFalse($user1Tasks->contains($task2));

        $this->assertTrue($user2Tasks->contains($task2));
        $this->assertFalse($user2Tasks->contains($task1));
    }

    /** @test */
    public function task_has_correct_status_options()
    {
        $validStatuses = ['pending', 'in_progress', 'completed'];

        foreach ($validStatuses as $status) {
            $task = Task::create([
                'user_id' => $this->user->id,
                'title' => "Task with status {$status}",
                'status' => $status,
                'order' => 1
            ]);

            $this->assertEquals($status, $task->status);
        }
    }

    /** @test */
    public function admin_user_creation()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_admin' => true
        ]);

        $this->assertTrue($admin->isAdmin());

        $regularUser = User::factory()->create(['is_admin' => false]);
        $this->assertFalse($regularUser->isAdmin());
    }

    /** @test */
    public function task_belongs_to_user()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $this->assertEquals($this->user->id, $task->user->id);
        $this->assertEquals($this->user->name, $task->user->name);
    }

    /** @test */
    public function user_has_many_tasks()
    {
        $task1 = Task::factory()->create(['user_id' => $this->user->id]);
        $task2 = Task::factory()->create(['user_id' => $this->user->id]);

        $this->assertEquals(2, $this->user->tasks->count());
        $this->assertTrue($this->user->tasks->contains($task1));
        $this->assertTrue($this->user->tasks->contains($task2));
    }

    /** @test */
    public function task_can_be_updated()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'description' => 'Original Description',
            'status' => 'pending'
        ]);

        $task->update([
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function task_can_be_deleted()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $taskId = $task->id;

        $task->delete();

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    /** @test */
    public function task_order_can_be_changed()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'order' => 1
        ]);

        $task->update(['order' => 5]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'order' => 5
        ]);
    }

    /** @test */
    public function task_status_can_be_changed()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $task->update(['status' => 'completed']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function multiple_users_can_have_tasks_with_same_order()
    {
        $task1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'order' => 1
        ]);

        $task2 = Task::factory()->create([
            'user_id' => $this->otherUser->id,
            'order' => 1
        ]);

        $this->assertEquals(1, $task1->order);
        $this->assertEquals(1, $task2->order);
        $this->assertNotEquals($task1->user_id, $task2->user_id);
    }

    /** @test */
    public function task_can_be_created_without_description()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Task Without Description',
            'status' => 'pending',
            'order' => 1
        ]);

        $this->assertNull($task->description);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => null
        ]);
    }

    /** @test */
    public function task_requires_user_id()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Task::create([
            'title' => 'Task Without User',
            'status' => 'pending',
            'order' => 1
        ]);
    }

    /** @test */
    public function task_requires_title()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Task::create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'order' => 1
        ]);
    }

    /** @test */
    public function admin_can_access_any_task()
    {
        $userTask = Task::factory()->create(['user_id' => $this->user->id]);
        $otherUserTask = Task::factory()->create(['user_id' => $this->otherUser->id]);

        // Admin puede ver ambas tareas según la policy
        $this->assertTrue($this->admin->can('view', $userTask));
        $this->assertTrue($this->admin->can('view', $otherUserTask));
        $this->assertTrue($this->admin->can('update', $userTask));
        $this->assertTrue($this->admin->can('delete', $otherUserTask));
    }

    /** @test */
    public function user_cannot_access_other_users_tasks()
    {
        $otherUserTask = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $this->assertFalse($this->user->can('view', $otherUserTask));
        $this->assertFalse($this->user->can('update', $otherUserTask));
        $this->assertFalse($this->user->can('delete', $otherUserTask));
    }

    /** @test */
    public function user_can_access_their_own_tasks()
    {
        $userTask = Task::factory()->create(['user_id' => $this->user->id]);

        $this->assertTrue($this->user->can('view', $userTask));
        $this->assertTrue($this->user->can('update', $userTask));
        $this->assertTrue($this->user->can('delete', $userTask));
    }

    /** @test */
    public function tasks_are_ordered_correctly_within_status()
    {
        $task1 = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'order' => 2,
            'title' => 'Second Task'
        ]);

        $task2 = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'order' => 1,
            'title' => 'First Task'
        ]);

        $pendingTasks = Task::where('user_id', $this->user->id)
            ->where('status', 'pending')
            ->orderBy('order')
            ->get();

        $this->assertEquals('First Task', $pendingTasks->first()->title);
        $this->assertEquals('Second Task', $pendingTasks->last()->title);
    }

    /** @test */
    public function task_creation_sets_correct_default_order()
    {
        // Create first task
        $task1 = Task::create([
            'user_id' => $this->user->id,
            'title' => 'First Task',
            'status' => 'pending',
            'order' => 1
        ]);

        // Simular creación de segunda tarea con orden siguiente
        $maxOrder = Task::where('user_id', $this->user->id)
            ->where('status', 'pending')
            ->max('order') ?? 0;

        $task2 = Task::create([
            'user_id' => $this->user->id,
            'title' => 'Second Task',
            'status' => 'pending',
            'order' => $maxOrder + 1
        ]);

        $this->assertEquals(1, $task1->order);
        $this->assertEquals(2, $task2->order);
    }

    /** @test */
    public function task_validation_works_with_long_strings()
    {
        // Very long title
        $longTitle = str_repeat('a', 256);

        // No debería fallar la creación del modelo (la validación es a nivel de request/form)
        $task = Task::create([
            'user_id' => $this->user->id,
            'title' => $longTitle,
            'status' => 'pending',
            'order' => 1
        ]);

        $this->assertEquals($longTitle, $task->title);
    }

    /** @test */
    public function task_can_move_between_different_statuses()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Mover de pending a in_progress
        $task->update(['status' => 'in_progress']);
        $this->assertEquals('in_progress', $task->fresh()->status);

        // Mover de in_progress a completed
        $task->update(['status' => 'completed']);
        $this->assertEquals('completed', $task->fresh()->status);

        // Mover de completed de vuelta a pending
        $task->update(['status' => 'pending']);
        $this->assertEquals('pending', $task->fresh()->status);
    }
}
