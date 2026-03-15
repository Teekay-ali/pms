<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskStatusChanged;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::with(['project', 'assignee', 'attachments.uploader'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Tasks/Index', [
            'tasks'    => $tasks,
            'projects' => Project::select('id', 'name')->orderBy('name')->get(),
            'users'    => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Task::class);

        return Inertia::render('Tasks/Create', [
            'projects' => Project::select('id', 'name')->orderBy('name')->get(),
            'users'    => User::select('id', 'name')->orderBy('name')->get(),
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
            'start_date' => 'nullable|date',
            'due_date'    => 'nullable|date',
            'priority'    => 'required|in:low,medium,high,critical',
            'status'      => 'required|in:pending,in_progress,review,completed,blocked',
        ]);

        $task = Task::create($validated);
        $task->load(['project', 'assignee']);

        // Notify assignee
        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            $task->assignee->notify(new TaskAssigned($task));
        }

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

        return Inertia::render('Tasks/Edit', [
            'task'     => $task,
            'projects' => Project::select('id', 'name')->orderBy('name')->get(),
            'users'    => User::select('id', 'name')->orderBy('name')->get(),
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
            'start_date' => 'nullable|date',
            'due_date'    => 'nullable|date',
            'priority'    => 'required|in:low,medium,high,critical',
            'status'      => 'required|in:pending,in_progress,review,completed,blocked',
        ]);

        $oldStatus     = $task->status;
        $oldAssignedTo = $task->assigned_to;

        $task->update($validated);
        $task->load(['project', 'assignee']);

        // Notify on new assignment
        if (
            $task->assigned_to &&
            $task->assigned_to !== $oldAssignedTo &&
            $task->assigned_to !== auth()->id()
        ) {
            $task->assignee->notify(new TaskAssigned($task));
        }

        // Notify on status change
        if ($oldStatus !== $task->status) {
            $recipients = collect();

            if ($task->assignee && $task->assignee->id !== auth()->id()) {
                $recipients->push($task->assignee);
            }

            if ($task->project?->project_manager_id) {
                $manager = User::find($task->project->project_manager_id);
                if ($manager && $manager->id !== auth()->id()) {
                    $recipients->push($manager);
                }
            }

            foreach ($recipients->unique('id') as $user) {
                $user->notify(new TaskStatusChanged($task, $oldStatus, auth()->user()));
            }
        }

        return redirect()->route('tasks.index')
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
