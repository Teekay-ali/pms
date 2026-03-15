<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        // ── Stats ──────────────────────────────────────────
        $activeProjects  = Project::where('status', 'active')->count();
        $openTasks       = Task::whereNotIn('status', ['completed'])->count();
        $tasksDueToday   = Task::whereNotIn('status', ['completed'])
            ->whereDate('due_date', today())
            ->count();
        $totalBudget     = Project::sum('budget');
        $pendingExpenses = Expense::where('status', 'pending')->count();

        // ── Recent projects (last 5, non-cancelled) ────────
        $recentProjects = Project::with('projectManager:id,name')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'status'   => $p->status,
                'progress' => $p->tasks_count > 0
                    ? (int) round(($p->completed_tasks_count / $p->tasks_count) * 100)
                    : 0,
                'manager'  => $p->projectManager?->name,
                'due'      => $p->end_date?->format('M j, Y'),
                'budget'   => $p->budget,
            ]);

        // ── Upcoming tasks (next 5 non-completed by due date) ──
        $upcomingTasks = Task::with(['project:id,name', 'assignee:id,name'])
            ->whereNotIn('status', ['completed'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id'       => $t->id,
                'name'     => $t->name,
                'project'  => $t->project?->name,
                'priority' => $t->priority,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'assignee' => $t->assignee
                    ? collect(explode(' ', $t->assignee->name))
                        ->map(fn ($n) => strtoupper($n[0]))
                        ->take(2)
                        ->join('')
                    : null,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeProjects'  => $activeProjects,
                'openTasks'       => $openTasks,
                'tasksDueToday'   => $tasksDueToday,
                'totalBudget'     => (float) $totalBudget,
                'pendingExpenses' => $pendingExpenses,
            ],
            'recentProjects' => $recentProjects,
            'upcomingTasks'  => $upcomingTasks,
        ]);
    }
}
