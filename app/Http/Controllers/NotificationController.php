<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(string $id)
    {
        auth()->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'All notifications cleared.');
    }

    // Polling endpoint — returns only unread count + latest 5
    public function poll()
    {
        $user = auth()->user();

        return response()->json([
            'unread_count'  => $user->unreadNotifications()->count(),
            'notifications' => $user->unreadNotifications()
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($n) => [
                    'id'         => $n->id,
                    'data'       => $n->data,
                    'created_at' => $n->created_at->diffForHumans(),
                ]),
        ]);
    }
}
