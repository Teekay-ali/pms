<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function viewAny(User $user): bool { return $user->can('finance.view'); }
    public function view(User $user, Bill $bill): bool { return $user->can('finance.view'); }
    public function create(User $user): bool { return $user->can('finance.bills.create'); }
    public function update(User $user, Bill $bill): bool {
        return $user->can('finance.bills.manage') && !in_array($bill->status, ['paid', 'cancelled']);
    }
    public function manage(User $user, Bill $bill): bool { return $user->can('finance.bills.manage'); }
    public function delete(User $user, Bill $bill): bool { return $user->can('finance.bills.manage'); }
}
