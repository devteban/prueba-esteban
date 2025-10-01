<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Task $task)
    {
        return $user->is_admin || $task->user_id === $user->id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Task $task)
    {
        return $user->is_admin || $task->user_id === $user->id;
    }

    public function delete(User $user, Task $task)
    {
        return $user->is_admin || $task->user_id === $user->id;
    }

    public function restore(User $user, Task $task)
    {
        return $user->is_admin || $task->user_id === $user->id;
    }

    public function forceDelete(User $user, Task $task)
    {
        return $user->is_admin || $task->user_id === $user->id;
    }
}
