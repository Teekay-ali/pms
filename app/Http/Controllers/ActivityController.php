<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(): Response
    {
        if (!auth()->user()->hasRole(['admin', 'ceo', 'project_manager'])) {
            abort(403);
        }

        $activities = Activity::with('causer')
            ->latest()
            ->paginate(30);

        return Inertia::render('Activity/Index', [
            'activities' => $activities,
        ]);
    }
}
