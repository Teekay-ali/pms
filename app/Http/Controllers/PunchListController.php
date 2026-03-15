<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PunchListItem;
use Illuminate\Http\Request;

class PunchListController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'description'     => 'required|string|max:500',
            'location_on_site'=> 'nullable|string|max:100',
            'assigned_to'     => 'nullable|exists:users,id',
            'priority'        => 'required|in:low,medium,high',
            'due_date'        => 'nullable|date',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['project_id'] = $project->id;

        PunchListItem::create($validated);

        return back()->with('success', 'Punch list item added.');
    }

    public function update(Request $request, Project $project, PunchListItem $item)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'description'     => 'required|string|max:500',
            'location_on_site'=> 'nullable|string|max:100',
            'assigned_to'     => 'nullable|exists:users,id',
            'priority'        => 'required|in:low,medium,high',
            'status'          => 'required|in:open,in_progress,completed',
            'due_date'        => 'nullable|date',
        ]);

        if ($validated['status'] === 'completed' && $item->status !== 'completed') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $item->update($validated);

        return back()->with('success', 'Punch list item updated.');
    }

    public function destroy(Project $project, PunchListItem $item)
    {
        $this->authorize('update', $project);

        $item->delete();

        return back()->with('success', 'Item deleted.');
    }
}
