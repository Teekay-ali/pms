<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionCategory;
use App\Models\DiscussionReaction;
use App\Models\DiscussionReply;
use App\Models\Project;
use App\Notifications\DiscussionReplied;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscussionController extends Controller
{
    public function index(Request $request): Response
    {
        $discussions = Discussion::with(['author', 'category', 'project'])
            ->withCount('reactions')
            ->whereNull('project_id')
            ->when($request->category, fn($q, $c) => $q->where('category_id', $c))
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_activity_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Discussions/Index', [
            'discussions' => $discussions,
            'categories'  => DiscussionCategory::withCount('discussions')->get(),
            'filters'     => $request->only(['category', 'search']),
        ]);
    }

    public function show(Discussion $discussion): Response
    {
        $discussion->increment('views');

        $discussion->load([
            'author',
            'category',
            'project',
            'attachments.uploader',
            'reactions',
            'replies' => fn($q) => $q->with(['author', 'reactions', 'attachments.uploader']),
        ]);

        return Inertia::render('Discussions/Show', [
            'discussion' => $discussion,
            'authId'     => auth()->id(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'body'        => 'required|string|max:10000',
            'category_id' => 'nullable|exists:discussion_categories,id',
            'project_id'  => 'nullable|exists:projects,id',
        ]);

        $validated['user_id']          = auth()->id();
        $validated['last_activity_at'] = now();

        Discussion::create($validated);

        return back()->with('success', 'Discussion created.');
    }

    public function update(Request $request, Discussion $discussion)
    {
        abort_if($discussion->user_id !== auth()->id() && !auth()->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'body'        => 'required|string|max:10000',
            'category_id' => 'nullable|exists:discussion_categories,id',
        ]);

        $discussion->update($validated);

        return back()->with('success', 'Discussion updated.');
    }

    public function destroy(Discussion $discussion)
    {
        abort_if($discussion->user_id !== auth()->id() && !auth()->user()->hasRole('admin'), 403);

        $discussion->delete();

        return redirect()->route('discussions.index')->with('success', 'Discussion deleted.');
    }

    public function pin(Discussion $discussion)
    {
        abort_if(!auth()->user()->hasRole(['admin', 'project_manager']), 403);

        $discussion->update(['is_pinned' => !$discussion->is_pinned]);

        return back()->with('success', $discussion->is_pinned ? 'Discussion pinned.' : 'Discussion unpinned.');
    }

    public function lock(Discussion $discussion)
    {
        abort_if(!auth()->user()->hasRole(['admin', 'project_manager']), 403);

        $discussion->update(['is_locked' => !$discussion->is_locked]);

        return back()->with('success', $discussion->is_locked ? 'Discussion locked.' : 'Discussion unlocked.');
    }

    // ── Replies ────────────────────────────────────────
    public function storeReply(Request $request, Discussion $discussion)
    {
        abort_if($discussion->is_locked, 403, 'This discussion is locked.');

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $reply = $discussion->replies()->create([
            'user_id' => auth()->id(),
            'body'    => $validated['body'],
        ]);

        $discussion->increment('replies_count');
        $discussion->update(['last_activity_at' => now()]);

        // Notify discussion author
        if ($discussion->user_id !== auth()->id()) {
            $discussion->author->notify(new DiscussionReplied($discussion, $reply, auth()->user()));
        }

        return back()->with('success', 'Reply posted.');
    }

    public function destroyReply(Discussion $discussion, DiscussionReply $reply)
    {
        abort_if($reply->user_id !== auth()->id() && !auth()->user()->hasRole('admin'), 403);

        $reply->delete();
        $discussion->decrement('replies_count');

        return back()->with('success', 'Reply deleted.');
    }

    // ── Reactions ──────────────────────────────────────
    public function react(Request $request, string $type, int $id)
    {
        $model = $type === 'discussion'
            ? Discussion::findOrFail($id)
            : DiscussionReply::findOrFail($id);

        $existing = $model->reactions()
            ->where('user_id', auth()->id())
            ->where('type', 'like')
            ->first();

        if ($existing) {
            $existing->delete();
            return back();
        }

        $model->reactions()->create([
            'user_id' => auth()->id(),
            'type'    => 'like',
        ]);

        return back();
    }
}
