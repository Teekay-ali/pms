<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $this->authorizeHR();

        return Inertia::render('HR/Departments/Index', [
            'departments' => Department::withCount('employees')
                ->with('manager')
                ->get(),
            'employees' => Employee::select('id', 'first_name', 'last_name', 'job_title')
                ->where('status', 'active')
                ->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->full_name, 'job_title' => $e->job_title]),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeHR();

        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string|max:255',
            'manager_id'  => 'nullable|exists:employees,id',
        ]);

        Department::create($validated);

        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department)
    {
        $this->authorizeHR();

        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:255',
            'manager_id'  => 'nullable|exists:employees,id',
        ]);

        $department->update($validated);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $this->authorizeHR();

        if ($department->employees()->count()) {
            return back()->with('error', 'Cannot delete department with employees.');
        }

        $department->delete();

        return back()->with('success', 'Department deleted.');
    }

    private function authorizeHR(): void
    {
        abort_if(!auth()->user()->hasRole(['admin', 'hr', 'ceo']), 403);
    }
}
