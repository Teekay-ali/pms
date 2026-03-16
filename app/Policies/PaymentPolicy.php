<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function create(User $user): bool { return $user->can('finance.payments.manage'); }
    public function update(User $user, Payment $payment): bool { return $user->can('finance.payments.manage'); }
    public function delete(User $user, Payment $payment): bool { return $user->can('finance.payments.manage'); }

    public function viewAny(User $user): bool
    {
        return $user->can('finance.view');
    }
}
