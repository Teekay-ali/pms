<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Project;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Bill::class);

        $bills    = Bill::with(['project', 'vendor', 'creator', 'approvedBy'])->latest()->paginate(15);
        $projects = Project::select('id', 'name')->orderBy('name')->get();
        $vendors  = Vendor::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Finance/Bills/Index', [
            'bills'    => $bills,
            'projects' => $projects,
            'vendors'  => $vendors,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Bill::class);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'vendor_id'   => 'nullable|exists:vendors,id',
            'description' => 'required|string|max:1000',
            'reference'   => 'nullable|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'issue_date'  => 'required|date',
            'due_date'    => 'required|date|after_or_equal:issue_date',
            'notes'       => 'nullable|string|max:2000',
        ]);

        $validated['created_by']  = auth()->id();
        $validated['bill_number'] = $this->nextNumber();
        $validated['status']      = 'pending';

        Bill::create($validated);

        return redirect()->back()->with('success', 'Bill created.');
    }

    public function update(Request $request, Bill $bill)
    {
        $this->authorize('update', $bill);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'vendor_id'   => 'nullable|exists:vendors,id',
            'description' => 'required|string|max:1000',
            'reference'   => 'nullable|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'issue_date'  => 'required|date',
            'due_date'    => 'required|date|after_or_equal:issue_date',
            'notes'       => 'nullable|string|max:2000',
        ]);

        $bill->update($validated);

        return redirect()->back()->with('success', 'Bill updated.');
    }

    public function approve(Bill $bill)
    {
        $this->authorize('manage', $bill);

        $bill->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Bill approved.');
    }

    public function destroy(Bill $bill)
    {
        $this->authorize('delete', $bill);
        $bill->delete();
        return redirect()->back()->with('success', 'Bill deleted.');
    }

    private function nextNumber(): string
    {
        $last = Bill::withTrashed()->max('id') ?? 0;
        return 'BILL-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
