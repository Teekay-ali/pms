<?php


namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueFinance extends Command
{
    protected $signature = 'finance:mark-overdue';
    protected $description = 'Mark overdue invoices and bills';

    public function handle(): void
    {
        Invoice::whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        Bill::whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);
    }
}
