<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use App\Models\Project;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class DailyLogController extends Controller
{
    public function __construct(private WeatherService $weather) {}

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'date'            => 'required|date',
            'work_performed'  => 'required|string|max:2000',
            'workers_on_site' => 'required|integer|min:0',
            'equipment_used'  => 'nullable|string|max:500',
            'delays_issues'   => 'nullable|string|max:1000',
            'weather'         => 'nullable|string|max:100',
            'temperature'     => 'nullable|numeric',
        ]);

        // Check for duplicate date
        if ($project->dailyLogs()->whereDate('date', $validated['date'])->exists()) {
            return back()->with('error', 'A daily log for this date already exists.');
        }

        // Auto-fetch weather if project has location and no manual weather provided
        if ($project->location && empty($validated['weather'])) {
            $weatherData = $this->weather->getWeather($project->location);
            if ($weatherData) {
                $validated['weather']      = $weatherData['weather'];
                $validated['temperature']  = $weatherData['temperature'];
                $validated['weather_icon'] = $weatherData['icon'];
            }
        }

        $validated['logged_by']  = auth()->id();
        $validated['project_id'] = $project->id;

        DailyLog::create($validated);

        return back()->with('success', 'Daily log saved.');
    }

    public function update(Request $request, Project $project, DailyLog $dailyLog)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'work_performed'  => 'required|string|max:2000',
            'workers_on_site' => 'required|integer|min:0',
            'equipment_used'  => 'nullable|string|max:500',
            'delays_issues'   => 'nullable|string|max:1000',
            'weather'         => 'nullable|string|max:100',
            'temperature'     => 'nullable|numeric',
        ]);

        $dailyLog->update($validated);

        return back()->with('success', 'Daily log updated.');
    }

    public function destroy(Project $project, DailyLog $dailyLog)
    {
        $this->authorize('update', $project);

        $dailyLog->delete();

        return back()->with('success', 'Daily log deleted.');
    }

    // API endpoint to fetch weather for a project
    public function fetchWeather(Project $project)
    {
        if (!$project->location) {
            return response()->json(['error' => 'No location set for this project.'], 422);
        }

        $data = $this->weather->getWeather($project->location);

        if (!$data) {
            return response()->json(['error' => 'Could not fetch weather data.'], 422);
        }

        return response()->json($data);
    }
}
