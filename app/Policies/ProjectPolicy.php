<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view') ||
            $project->members->contains($user->id) ||
            $project->project_manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.update') ||
            $project->project_manager_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.delete');
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->hasRole('admin');
    }
}
