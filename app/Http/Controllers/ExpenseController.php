<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = Expense::with(['project', 'approvedBy'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Expense::class);

        $projects = Project::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Expenses/Create', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Expense::class);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'date'        => 'required|date',
        ]);

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense): Response
    {
        $this->authorize('view', $expense);

        $expense->load(['project', 'approvedBy']);

        return Inertia::render('Expenses/Show', [
            'expense' => $expense,
        ]);
    }

    public function edit(Expense $expense): Response
    {
        $this->authorize('update', $expense);

        $projects = Project::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Expenses/Edit', [
            'expense'  => $expense,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'date'        => 'required|date',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function approve(Request $request, Expense $expense)
    {
        $this->authorize('approve', $expense);

        $expense->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->back()
            ->with('success', 'Expense approved successfully.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
