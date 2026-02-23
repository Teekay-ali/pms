<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('expenses.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->can('expenses.view');
    }

    public function create(User $user): bool
    {
        return $user->can('expenses.create');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can('expenses.update') &&
            $expense->status === 'pending';
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->hasRole('admin');
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->can('expenses.approve');
    }

    public function restore(User $user, Expense $expense): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Expense $expense): bool
    {
        return $user->hasRole('admin');
    }
}
