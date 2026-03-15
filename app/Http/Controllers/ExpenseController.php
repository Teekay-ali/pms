<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ExpenseApproved;
use App\Notifications\ExpenseRejected;
use App\Notifications\ExpenseSubmitted;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = Expense::with(['project', 'approvedBy', 'creator', 'attachments.uploader'])
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

        $validated['created_by'] = auth()->id();

        $expense = Expense::create($validated);
        $expense->load('project');

        // Notify all users with expense approval permission
        $approvers = User::permission('expenses.approve')->get();
        foreach ($approvers as $approver) {
            if ($approver->id !== auth()->id()) {
                $approver->notify(new ExpenseSubmitted($expense, auth()->user()));
            }
        }

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

        // Notify the submitter
        if ($expense->created_by && $expense->created_by !== auth()->id()) {
            $expense->load('project');
            $expense->creator->notify(new ExpenseApproved($expense, auth()->user()));
        }

        return redirect()->back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->authorize('approve', $expense);

        $expense->update([
            'status'      => 'rejected',
            'approved_by' => $request->user()->id,
        ]);

        // Notify the submitter
        if ($expense->created_by && $expense->created_by !== auth()->id()) {
            $expense->load('project');
            $expense->creator->notify(new ExpenseRejected($expense, auth()->user()));
        }

        return redirect()->back()->with('success', 'Expense rejected.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->back()->with('success', 'Expense deleted.');
    }
}
