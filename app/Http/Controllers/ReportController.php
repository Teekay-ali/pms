<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesExport;
use App\Exports\FinanceExport;
use App\Exports\ProjectsExport;
use App\Exports\ResourcesExport;
use App\Models\Bill;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Resource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with(['expenses', 'tasks', 'projectManager'])
            ->withCount('tasks')
            ->get();

        return Inertia::render('Reports/Index', [
            'projects' => Project::select('id', 'name')->orderBy('name')->get(),
            'summary'  => [
                'total_projects'  => $projects->count(),
                'active_projects' => $projects->where('status', 'active')->count(),
                'total_budget'    => $projects->sum('budget'),
                'total_spent'     => $projects->sum(fn($p) => $p->expenses->where('status', 'approved')->sum('amount')),
            ],
        ]);
    }

    // ── Excel exports ──────────────────────────────────

    public function exportProjectsExcel(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        return Excel::download(new ProjectsExport($request->all()), 'projects-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportExpensesExcel(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        return Excel::download(new ExpensesExport($request->all()), 'expenses-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportFinanceExcel(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        return Excel::download(new FinanceExport($request->all()), 'finance-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportResourcesExcel(Request $request)
    {
        $this->authorize('viewAny', Project::class);
        return Excel::download(new ResourcesExport($request->all()), 'resources-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    // ── PDF exports ────────────────────────────────────

    public function exportProjectsPdf(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects   = Project::with(['expenses', 'tasks', 'projectManager'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->get();
        $totalSpent = $projects->sum(fn($p) => $p->expenses->where('status', 'approved')->sum('amount'));

        $pdf = Pdf::loadView('reports.projects', compact('projects', 'totalSpent'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('projects-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExpensesPdf(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $expenses = Expense::with(['project', 'creator', 'approvedBy'])
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->when($request->status,     fn($q, $s)  => $q->where('status', $s))
            ->when($request->from,       fn($q, $d)  => $q->whereDate('date', '>=', $d))
            ->when($request->to,         fn($q, $d)  => $q->whereDate('date', '<=', $d))
            ->orderBy('date', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.expenses', [
            'expenses' => $expenses,
            'filters'  => $request->only(['from', 'to']),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('expenses-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportFinancePdf(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $invoices = Invoice::with(['project'])
            ->when($request->from, fn($q, $d) => $q->whereDate('issue_date', '>=', $d))
            ->when($request->to,   fn($q, $d) => $q->whereDate('issue_date', '<=', $d))
            ->get();

        $bills = Bill::with(['project', 'vendor'])
            ->when($request->from, fn($q, $d) => $q->whereDate('issue_date', '>=', $d))
            ->when($request->to,   fn($q, $d) => $q->whereDate('issue_date', '<=', $d))
            ->get();

        $pdf = Pdf::loadView('reports.finance', compact('invoices', 'bills'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('finance-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportResourcesPdf(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $resources = Resource::with('project')
            ->when($request->project_id, fn($q, $id) => $q->where('project_id', $id))
            ->when($request->type,       fn($q, $t)  => $q->where('type', $t))
            ->get();

        $pdf = Pdf::loadView('reports.resources', compact('resources'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('resources-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
