<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinanceDashboardController extends Controller
{
    public function __invoke()
    {
        $this->authorize('viewAny', Invoice::class);

        // Invoice stats
        $invoiceStats = [
            'total_invoiced'   => Invoice::whereNotIn('status', ['draft', 'cancelled'])->sum('total'),
            'total_collected'  => Invoice::sum('amount_paid'),
            'outstanding'      => Invoice::whereNotIn('status', ['paid', 'cancelled'])->selectRaw('SUM(total - amount_paid) as bal')->value('bal') ?? 0,
            'overdue_count'    => Invoice::where('status', 'overdue')->count(),
        ];

        // Bill stats
        $billStats = [
            'total_billed'  => Bill::whereNotIn('status', ['draft', 'cancelled'])->sum('amount'),
            'total_paid'    => Bill::sum('amount_paid'),
            'outstanding'   => Bill::whereNotIn('status', ['paid', 'cancelled'])->selectRaw('SUM(amount - amount_paid) as bal')->value('bal') ?? 0,
            'overdue_count' => Bill::where('status', 'overdue')->count(),
        ];

        // Recent payments (last 10)
        $recentPayments = Payment::with(['creator', 'payable'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'amount'       => $p->amount,
                'method'       => $p->method,
                'payment_date' => $p->payment_date,
                'reference'    => $p->reference,
                'type'         => class_basename($p->payable_type),
                'payable_number' => $p->payable?->invoice_number ?? $p->payable?->bill_number ?? '—',
                'created_by'   => $p->creator?->name,
            ]);

        // Per-project finance summary (top 6 active)
        $projectSummary = Project::whereIn('status', ['active', 'planning'])
            ->withSum(['invoices as total_invoiced' => fn($q) => $q->whereNotIn('status', ['draft','cancelled'])], 'total')
            ->withSum('invoices as total_collected', 'amount_paid')
            ->withSum(['bills as total_billed' => fn($q) => $q->whereNotIn('status', ['draft','cancelled'])], 'amount')
            ->withSum('bills as total_bill_paid', 'amount_paid')
            ->limit(6)
            ->get(['id', 'name', 'budget', 'status']);

        return Inertia::render('Finance/Dashboard', [
            'invoiceStats'   => $invoiceStats,
            'billStats'      => $billStats,
            'recentPayments' => $recentPayments,
            'projectSummary' => $projectSummary,
        ]);
    }
}
