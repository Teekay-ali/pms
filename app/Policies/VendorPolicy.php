<?php

namespace App\Policies;

use App\Models\Vendor;
use App\Models\User;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vendors.view');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.view');
    }

    public function create(User $user): bool
    {
        return $user->can('vendors.create');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.update');
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.delete');
    }

    public function restore(User $user, Vendor $vendor): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Vendor $vendor): bool
    {
        return $user->hasRole('admin');
    }
}
