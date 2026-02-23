<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;

class ResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('resources.view');
    }

    public function view(User $user, Resource $resource): bool
    {
        return $user->can('resources.view');
    }

    public function create(User $user): bool
    {
        return $user->can('resources.create');
    }

    public function update(User $user, Resource $resource): bool
    {
        return $user->can('resources.update');
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $user->can('resources.delete');
    }

    public function restore(User $user, Resource $resource): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Resource $resource): bool
    {
        return $user->hasRole('admin');
    }
}
