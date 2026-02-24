<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Resource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Resource::class);

        $resources = Resource::with('project')
            ->latest()
            ->paginate(15);

        $projects = Project::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Resources/Index', [
            'resources' => $resources,
            'projects'  => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Resource::class);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'nullable|string|max:100',
            'quantity'   => 'required|numeric|min:0',
            'unit'       => 'nullable|string|max:50',
            'cost'       => 'nullable|numeric|min:0',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        Resource::create($validated);

        return redirect()->back()->with('success', 'Resource created successfully.');
    }

    public function update(Request $request, Resource $resource)
    {
        $this->authorize('update', $resource);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'nullable|string|max:100',
            'quantity'   => 'required|numeric|min:0',
            'unit'       => 'nullable|string|max:50',
            'cost'       => 'nullable|numeric|min:0',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $resource->update($validated);

        return redirect()->back()->with('success', 'Resource updated successfully.');
    }

    public function destroy(Resource $resource)
    {
        $this->authorize('delete', $resource);

        $resource->delete();

        return redirect()->back()->with('success', 'Resource deleted.');
    }
}
