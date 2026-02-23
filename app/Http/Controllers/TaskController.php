<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::with(['project', 'assignee'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Task::class);

        $projects = Project::select('id', 'name')->orderBy('name')->get();
        $users    = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Tasks/Create', [
            'projects' => $projects,
            'users'    => $users,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'priority'    => 'required|in:low,medium,high,critical',
            'status'      => 'required|in:pending,in_progress,review,completed,blocked',
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task): Response
    {
        $this->authorize('view', $task);

        $task->load(['project', 'assignee']);

        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    public function edit(Task $task): Response
    {
        $this->authorize('update', $task);

        $projects = Project::select('id', 'name')->orderBy('name')->get();
        $users    = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Tasks/Edit', [
            'task'     => $task,
            'projects' => $projects,
            'users'    => $users,
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'priority'    => 'required|in:low,medium,high,critical',
            'status'      => 'required|in:pending,in_progress,review,completed,blocked',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
