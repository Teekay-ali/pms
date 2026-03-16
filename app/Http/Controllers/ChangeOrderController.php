<?php


namespace App\Http\Controllers;

use App\Models\ChangeOrder;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChangeOrderController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', ChangeOrder::class);

        $changeOrders = ChangeOrder::with(['project', 'creator', 'approvedBy'])
            ->latest()
            ->paginate(15);

        $projects = Project::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('ChangeOrders/Index', [
            'changeOrders' => $changeOrders,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ChangeOrder::class);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'reason' => 'required|in:client_request,site_condition,design_change,unforeseen_work,other',
            'cost_impact' => 'required|numeric',
            'timeline_impact' => 'required|integer',
            'status' => 'required|in:draft,submitted',
        ]);

        $validated['created_by'] = auth()->id();
        if ($validated['status'] === 'submitted') {
            $validated['submitted_at'] = now();
        }

        ChangeOrder::create($validated);

        return redirect()->back()->with('success', 'Change order created.');
    }

    public function update(Request $request, ChangeOrder $changeOrder)
    {
        $this->authorize('update', $changeOrder);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'reason' => 'required|in:client_request,site_condition,design_change,unforeseen_work,other',
            'cost_impact' => 'required|numeric',
            'timeline_impact' => 'required|integer',
            'status' => 'required|in:draft,submitted',
        ]);

        if ($validated['status'] === 'submitted' && $changeOrder->status === 'draft') {
            $validated['submitted_at'] = now();
        }

        $changeOrder->update($validated);

        return redirect()->back()->with('success', 'Change order updated.');
    }

    public function approve(Request $request, ChangeOrder $changeOrder)
    {
        $this->authorize('approve', $changeOrder);

        \DB::transaction(function () use ($changeOrder) {
            $changeOrder = ChangeOrder::lockForUpdate()->findOrFail($changeOrder->id);

            if ($changeOrder->status !== 'submitted') {
                abort(422, 'Only submitted change orders can be approved.');
            }

            $changeOrder->project->increment('budget', $changeOrder->cost_impact);
            $changeOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Change order approved and budget updated.');
    }

    public function reject(Request $request, ChangeOrder $changeOrder)
    {
        $this->authorize('approve', $changeOrder);

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($changeOrder->status !== 'submitted') {
            return redirect()->back()->with('error', 'Only submitted change orders can be rejected.');
        }

        $changeOrder->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Change order rejected.');
    }

    public function destroy(ChangeOrder $changeOrder)
    {
        $this->authorize('delete', $changeOrder);

        $changeOrder->delete();

        return redirect()->back()->with('success', 'Change order deleted.');
    }
}
