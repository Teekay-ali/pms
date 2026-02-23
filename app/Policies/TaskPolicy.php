<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view') ||
            $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update') ||
            $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete');
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }
}
