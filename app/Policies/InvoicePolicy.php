<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool { return $user->can('finance.view'); }
    public function view(User $user, Invoice $invoice): bool { return $user->can('finance.view'); }
    public function create(User $user): bool { return $user->can('finance.invoices.create'); }
    public function update(User $user, Invoice $invoice): bool {
        return $user->can('finance.invoices.manage') && !in_array($invoice->status, ['paid', 'cancelled']);
    }
    public function delete(User $user, Invoice $invoice): bool { return $user->can('finance.invoices.manage'); }
}
