<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::with(['projectManager', 'creator'])
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed')])
            ->latest()
            ->paginate(15);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'users'    => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Project::class);

        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Projects/Create', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'budget'             => 'nullable|numeric|min:0',
            'status'             => 'required|in:planning,active,on_hold,completed,cancelled',
            'project_manager_id' => 'nullable|exists:users,id',
            'members'            => 'nullable|array',
            'members.*'          => 'exists:users,id',
        ]);

        $project = Project::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        if (!empty($validated['members'])) {
            $project->members()->sync($validated['members']);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load([
            'projectManager',
            'creator',
            'members',
            'tasks' => fn($q) => $q->with('assignee')->orderBy('due_date'),
            'expenses' => fn($q) => $q->with('approvedBy')->orderBy('date', 'desc'),
            'resources',
        ]);

        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed'),
            'tasks as in_progress_tasks_count' => fn($q) => $q->where('status', 'in_progress'),
            'tasks as pending_tasks_count' => fn($q) => $q->where('status', 'pending'),
        ]);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'users'   => \App\Models\User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        $project->load('members');
        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'users'   => $users,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'budget'             => 'nullable|numeric|min:0',
            'status'             => 'required|in:planning,active,on_hold,completed,cancelled',
            'project_manager_id' => 'nullable|exists:users,id',
            'members'            => 'nullable|array',
            'members.*'          => 'exists:users,id',
        ]);

        $project->update($validated);

        if (isset($validated['members'])) {
            $project->members()->sync($validated['members']);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
