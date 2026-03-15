<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeHR();

        $employees = Employee::with(['department', 'user'])
            ->when($request->search, fn($q, $s) =>
            $q->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('employee_number', 'like', "%{$s}%")
            )
            ->when($request->department, fn($q, $d) => $q->where('department_id', $d))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('HR/Employees/Index', [
            'employees'   => $employees,
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
            'departments' => Department::select('id', 'name')->get(),
            'filters'     => $request->only(['search', 'department', 'status']),
            'stats'       => [
                'total'      => Employee::count(),
                'active'     => Employee::where('status', 'active')->count(),
                'on_leave'   => Employee::where('status', 'on_leave')->count(),
                'inactive'   => Employee::whereIn('status', ['inactive', 'terminated'])->count(),
            ],
        ]);
    }

    public function show(Employee $employee): Response
    {
        $this->authorizeHR();

        $employee->load([
            'department',
            'user',
            'leaveRequests.approver',
            'attachments.uploader',
        ]);

        return Inertia::render('HR/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeHR();

        $validated = $request->validate([
            'first_name'                   => 'required|string|max:100',
            'last_name'                    => 'required|string|max:100',
            'email'                        => 'required|email|unique:employees,email',
            'phone'                        => 'nullable|string|max:20',
            'date_of_birth'                => 'nullable|date',
            'gender'                       => 'nullable|in:male,female,other',
            'nationality'                  => 'nullable|string|max:100',
            'id_number'                    => 'nullable|string|max:50',
            'address'                      => 'nullable|string',
            'emergency_contact_name'       => 'nullable|string|max:100',
            'emergency_contact_phone'      => 'nullable|string|max:20',
            'emergency_contact_relation'   => 'nullable|string|max:50',
            'employee_number'              => 'nullable|string|max:50|unique:employees,employee_number',
            'job_title'                    => 'nullable|string|max:100',
            'department_id'                => 'nullable|exists:departments,id',
            'employment_type'              => 'required|in:full_time,part_time,contract,intern',
            'status'                       => 'required|in:active,inactive,terminated,on_leave',
            'hire_date'                    => 'nullable|date',
            'salary'                       => 'nullable|numeric|min:0',
            'bank_name'                    => 'nullable|string|max:100',
            'bank_account'                 => 'nullable|string|max:50',
            'annual_leave_balance'         => 'integer|min:0',
            'sick_leave_balance'           => 'integer|min:0',
            'user_id'                      => 'nullable|exists:users,id',
        ]);

        Employee::create($validated);

        return back()->with('success', 'Employee created successfully.');
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeHR();

        $validated = $request->validate([
            'first_name'                   => 'required|string|max:100',
            'last_name'                    => 'required|string|max:100',
            'email'                        => 'required|email|unique:employees,email,' . $employee->id,
            'phone'                        => 'nullable|string|max:20',
            'date_of_birth'                => 'nullable|date',
            'gender'                       => 'nullable|in:male,female,other',
            'nationality'                  => 'nullable|string|max:100',
            'id_number'                    => 'nullable|string|max:50',
            'address'                      => 'nullable|string',
            'emergency_contact_name'       => 'nullable|string|max:100',
            'emergency_contact_phone'      => 'nullable|string|max:20',
            'emergency_contact_relation'   => 'nullable|string|max:50',
            'employee_number'              => 'nullable|string|max:50|unique:employees,employee_number,' . $employee->id,
            'job_title'                    => 'nullable|string|max:100',
            'department_id'                => 'nullable|exists:departments,id',
            'employment_type'              => 'required|in:full_time,part_time,contract,intern',
            'status'                       => 'required|in:active,inactive,terminated,on_leave',
            'hire_date'                    => 'nullable|date',
            'termination_date'             => 'nullable|date',
            'salary'                       => 'nullable|numeric|min:0',
            'bank_name'                    => 'nullable|string|max:100',
            'bank_account'                 => 'nullable|string|max:50',
            'annual_leave_balance'         => 'integer|min:0',
            'sick_leave_balance'           => 'integer|min:0',
            'user_id'                      => 'nullable|exists:users,id',
        ]);

        $employee->update($validated);

        return back()->with('success', 'Employee updated successfully.');
    }

    public function uploadPhoto(Request $request, Employee $employee)
    {
        $this->authorizeHR();

        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $path = $request->file('photo')->store("employees/photos", 'public');
        $employee->update(['photo' => $path]);

        return back()->with('success', 'Photo updated.');
    }

    public function destroy(Employee $employee)
    {
        $this->authorizeHR();

        $employee->delete();

        return back()->with('success', 'Employee removed.');
    }

    private function authorizeHR(): void
    {
        abort_if(!auth()->user()->hasRole(['admin', 'hr', 'ceo']), 403);
    }
}
