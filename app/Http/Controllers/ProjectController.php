<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Notifications\AddedToProject;
use App\Notifications\ProjectCreated;
use App\Notifications\ProjectStatusChanged;
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

        return Inertia::render('Projects/Create', [
            'users' => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'location'           => 'nullable|string|max:255',
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

        // Notify admins, CEOs, and project managers about new project
        $recipients = User::role(['admin', 'ceo', 'project_manager'])->get();
        foreach ($recipients as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new ProjectCreated($project, auth()->user()));
            }
        }

        // Notify added members
        $memberIds = $validated['members'] ?? [];
        $members   = User::whereIn('id', $memberIds)->get();
        foreach ($members as $member) {
            if ($member->id !== auth()->id()) {
                $member->notify(new AddedToProject($project, auth()->user()));
            }
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
            'attachments.uploader',
            'dailyLogs.logger',
            'tasks'    => fn($q) => $q->with('assignee')->orderBy('due_date'),
            'expenses' => fn($q) => $q->with('approvedBy')->orderBy('date', 'desc'),
            'resources',
            'activities.causer',
        ]);

        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count'  => fn($q) => $q->where('status', 'completed'),
            'tasks as in_progress_tasks_count' => fn($q) => $q->where('status', 'in_progress'),
            'tasks as pending_tasks_count'    => fn($q) => $q->where('status', 'pending'),
        ]);

//        dd($project->location);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'users'   => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        $project->load('members');

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'users'   => User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'location'           => 'nullable|string|max:255',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'budget'             => 'nullable|numeric|min:0',
            'status'             => 'required|in:planning,active,on_hold,completed,cancelled',
            'project_manager_id' => 'nullable|exists:users,id',
            'members'            => 'nullable|array',
            'members.*'          => 'exists:users,id',
        ]);

        $oldStatus    = $project->status;
        $oldMemberIds = $project->members()->pluck('users.id')->toArray();

        $project->update($validated);

        if (isset($validated['members'])) {
            $project->members()->sync($validated['members']);

            // Notify newly added members only
            $newMemberIds = array_diff($validated['members'], $oldMemberIds);
            $newMembers   = User::whereIn('id', $newMemberIds)->get();
            foreach ($newMembers as $member) {
                if ($member->id !== auth()->id()) {
                    $member->notify(new AddedToProject($project, auth()->user()));
                }
            }
        }

        // Notify on status change
        if ($oldStatus !== $project->status) {
            $project->load('members');
            $recipients = $project->members
                ->merge(User::role(['admin', 'ceo'])->get())
                ->unique('id');

            foreach ($recipients as $user) {
                if ($user->id !== auth()->id()) {
                    $user->notify(new ProjectStatusChanged($project, $oldStatus, auth()->user()));
                }
            }
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

    public function addMember(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Prevent duplicate
        if ($project->members()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'User is already a member.');
        }

        $project->members()->attach($validated['user_id']);

        $newMember = User::find($validated['user_id']);
        if ($newMember->id !== auth()->id()) {
            $newMember->notify(new AddedToProject($project, auth()->user()));
        }

        return back()->with('success', "{$newMember->name} added to project.");
    }

    public function removeMember(Request $request, Project $project, User $user)
    {
        $this->authorize('update', $project);

        // Prevent removing the project manager
        if ($project->project_manager_id === $user->id) {
            return back()->with('error', 'Cannot remove the project manager.');
        }

        $project->members()->detach($user->id);

        return back()->with('success', "{$user->name} removed from project.");
    }

}
