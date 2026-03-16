<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(): Response
    {
        // Consistent with the rest of the app
        abort_if(
            !auth()->user()->hasRole(['admin', 'ceo', 'project_manager']),
            403
        );

        $activities = Activity::with('causer')
            ->latest()
            ->paginate(30);

        return Inertia::render('Activity/Index', [
            'activities' => $activities,
        ]);
    }
}
