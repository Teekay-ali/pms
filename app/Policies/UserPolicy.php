<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.update');
    }

    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        return $user->can('users.delete') && $user->id !== $model->id;
    }

    public function assignRole(User $user, User $model): bool
    {
        // Cannot change your own role
        return $user->can('roles.assign') && $user->id !== $model->id;
    }
}
