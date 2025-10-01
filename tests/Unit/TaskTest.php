<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $task = new Task();
        $fillable = ['user_id', 'title', 'description', 'status', 'order'];

        $this->assertEquals($fillable, $task->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $task = new Task();
        $expectedCasts = [
            'user_id' => 'integer',
            'order' => 'integer',
        ];

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $task->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $task = new Task();
        $this->assertInstanceOf(BelongsTo::class, $task->user());
    }

    /** @test */
    public function user_can_create_and_move_task()
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Prueba',
            'description' => 'Demo',
            'status' => 'pending',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'pending',
        ]);

        $task->status = 'in_progress';
        $task->order = 2;
        $task->save();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
            'order' => 2,
        ]);
    }

    /** @test */
    public function task_can_be_created_with_all_attributes()
    {
        $user = User::factory()->create();

        $taskData = [
            'user_id' => $user->id,
            'title' => 'Test Task Title',
            'description' => 'Test Task Description',
            'status' => 'pending',
            'order' => 5,
        ];

        $task = Task::create($taskData);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($taskData['title'], $task->title);
        $this->assertEquals($taskData['description'], $task->description);
        $this->assertEquals($taskData['status'], $task->status);
        $this->assertEquals($taskData['order'], $task->order);
        $this->assertEquals($taskData['user_id'], $task->user_id);
    }

    /** @test */
    public function task_can_be_created_without_description()
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Task Without Description',
            'status' => 'pending',
            'order' => 1,
        ]);

        $this->assertNull($task->description);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => null,
        ]);
    }

    /** @test */
    public function task_relationship_with_user_works_correctly()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
        $this->assertEquals($user->name, $task->user->name);
        $this->assertEquals($user->email, $task->user->email);
    }

    /** @test */
    public function task_attributes_are_cast_properly()
    {
        $user = User::factory()->create();

        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'Test Task',
            'status' => 'pending',
            'order' => '10', // String que debería convertirse a integer
        ]);

        $this->assertIsInt($task->user_id);
        $this->assertIsInt($task->order);
        $this->assertEquals(10, $task->order);
    }

    /** @test */
    public function task_can_have_different_status_values()
    {
        $user = User::factory()->create();
        $validStatuses = ['pending', 'in_progress', 'completed'];

        foreach ($validStatuses as $status) {
            $task = Task::create([
                'user_id' => $user->id,
                'title' => "Task with {$status} status",
                'status' => $status,
                'order' => 1,
            ]);

            $this->assertEquals($status, $task->status);
            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'status' => $status,
            ]);
        }
    }

    /** @test */
    public function task_has_auditable_trait()
    {
        $task = new Task();
        $traits = class_uses_recursive($task);

        $this->assertContains('OwenIt\Auditing\Auditable', $traits);
    }

    /** @test */
    public function task_implements_auditable_contract()
    {
        $task = new Task();
        $interfaces = class_implements($task);

        $this->assertContains('OwenIt\Auditing\Contracts\Auditable', $interfaces);
    }

    /** @test */
    public function task_order_can_be_updated()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'order' => 1
        ]);

        $task->update(['order' => 5]);

        $this->assertEquals(5, $task->fresh()->order);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'order' => 5,
        ]);
    }

    /** @test */
    public function multiple_tasks_can_have_same_order_for_different_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $task1 = Task::factory()->create([
            'user_id' => $user1->id,
            'order' => 1
        ]);

        $task2 = Task::factory()->create([
            'user_id' => $user2->id,
            'order' => 1
        ]);

        $this->assertEquals(1, $task1->order);
        $this->assertEquals(1, $task2->order);
        $this->assertNotEquals($task1->user_id, $task2->user_id);
    }
}
