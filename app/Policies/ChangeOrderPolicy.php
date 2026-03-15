<?php

namespace App\Policies;

use App\Models\ChangeOrder;
use App\Models\User;

class ChangeOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('change_orders.view');
    }

    public function view(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->can('change_orders.view');
    }

    public function create(User $user): bool
    {
        return $user->can('change_orders.create');
    }

    public function update(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->can('change_orders.update') &&
            in_array($changeOrder->status, ['draft', 'submitted']);
    }

    public function delete(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->can('change_orders.delete') &&
            in_array($changeOrder->status, ['draft', 'rejected']);
    }

    public function approve(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->can('change_orders.approve');
    }
}
