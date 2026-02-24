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

        $projects = Project::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Expense::class);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'date'        => 'required|date',
        ]);

        Expense::create($validated);

        return redirect()->back()->with('success', 'Expense created successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'date'        => 'required|date',
        ]);

        $expense->update($validated);

        return redirect()->back()->with('success', 'Expense updated successfully.');
    }

    public function approve(Request $request, Expense $expense)
    {
        $this->authorize('approve', $expense);

        $expense->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->authorize('approve', $expense); // same permission gate

        $expense->update([
            'status'      => 'rejected',
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Expense rejected.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->back()->with('success', 'Expense deleted.');
    }
}
