<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TaskPolicy $policy;
    private User $user;
    private User $admin;
    private User $otherUser;
    private Task $userTask;
    private Task $otherUserTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TaskPolicy();

                // Create test users
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
        $this->otherUser = User::factory()->create(['is_admin' => false]);

        // Create test tasks
        $this->userTask = Task::factory()->create(['user_id' => $this->user->id]);
        $this->otherUserTask = Task::factory()->create(['user_id' => $this->otherUser->id]);
    }

    /** @test */
    public function view_any_allows_all_authenticated_users()
    {
        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->viewAny($this->admin));
        $this->assertTrue($this->policy->viewAny($this->otherUser));
    }

    /** @test */
    public function view_allows_task_owner()
    {
        $this->assertTrue($this->policy->view($this->user, $this->userTask));
    }

    /** @test */
    public function view_allows_admin_to_see_any_task()
    {
        $this->assertTrue($this->policy->view($this->admin, $this->userTask));
        $this->assertTrue($this->policy->view($this->admin, $this->otherUserTask));
    }

    /** @test */
    public function view_denies_other_users()
    {
        $this->assertFalse($this->policy->view($this->user, $this->otherUserTask));
        $this->assertFalse($this->policy->view($this->otherUser, $this->userTask));
    }

    /** @test */
    public function create_allows_all_authenticated_users()
    {
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->create($this->admin));
        $this->assertTrue($this->policy->create($this->otherUser));
    }

    /** @test */
    public function update_allows_task_owner()
    {
        $this->assertTrue($this->policy->update($this->user, $this->userTask));
    }

    /** @test */
    public function update_allows_admin_to_modify_any_task()
    {
        $this->assertTrue($this->policy->update($this->admin, $this->userTask));
        $this->assertTrue($this->policy->update($this->admin, $this->otherUserTask));
    }

    /** @test */
    public function update_denies_other_users()
    {
        $this->assertFalse($this->policy->update($this->user, $this->otherUserTask));
        $this->assertFalse($this->policy->update($this->otherUser, $this->userTask));
    }

    /** @test */
    public function delete_allows_task_owner()
    {
        $this->assertTrue($this->policy->delete($this->user, $this->userTask));
    }

    /** @test */
    public function delete_allows_admin_to_delete_any_task()
    {
        $this->assertTrue($this->policy->delete($this->admin, $this->userTask));
        $this->assertTrue($this->policy->delete($this->admin, $this->otherUserTask));
    }

    /** @test */
    public function delete_denies_other_users()
    {
        $this->assertFalse($this->policy->delete($this->user, $this->otherUserTask));
        $this->assertFalse($this->policy->delete($this->otherUser, $this->userTask));
    }

    /** @test */
    public function restore_allows_task_owner()
    {
        $this->assertTrue($this->policy->restore($this->user, $this->userTask));
    }

    /** @test */
    public function restore_allows_admin_to_restore_any_task()
    {
        $this->assertTrue($this->policy->restore($this->admin, $this->userTask));
        $this->assertTrue($this->policy->restore($this->admin, $this->otherUserTask));
    }

    /** @test */
    public function restore_denies_other_users()
    {
        $this->assertFalse($this->policy->restore($this->user, $this->otherUserTask));
        $this->assertFalse($this->policy->restore($this->otherUser, $this->userTask));
    }

    /** @test */
    public function force_delete_allows_task_owner()
    {
        $this->assertTrue($this->policy->forceDelete($this->user, $this->userTask));
    }

    /** @test */
    public function force_delete_allows_admin_to_force_delete_any_task()
    {
        $this->assertTrue($this->policy->forceDelete($this->admin, $this->userTask));
        $this->assertTrue($this->policy->forceDelete($this->admin, $this->otherUserTask));
    }

    /** @test */
    public function force_delete_denies_other_users()
    {
        $this->assertFalse($this->policy->forceDelete($this->user, $this->otherUserTask));
        $this->assertFalse($this->policy->forceDelete($this->otherUser, $this->userTask));
    }
}
