<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeHR();

        $leaves = LeaveRequest::with(['employee.department', 'approver'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('HR/Leave/Index', [
            'leaves'    => $leaves,
            'employees' => Employee::select('id', 'first_name', 'last_name')
                ->where('status', 'active')->get()
                ->map(fn($e) => ['id' => $e->id, 'name' => $e->full_name]),
            'filters'   => $request->only(['status', 'type']),
            'stats'     => [
                'pending'  => LeaveRequest::where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('status', 'approved')->count(),
                'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeHR();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:annual,sick,unpaid,maternity,paternity,other',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string|max:500',
        ]);

        // Calculate days (excluding weekends)
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end   = \Carbon\Carbon::parse($validated['end_date']);
        $days  = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend()) $days++;
            $current->addDay();
        }
        $validated['days'] = $days;

        LeaveRequest::create($validated);

        return back()->with('success', 'Leave request submitted.');
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        $this->authorizeHR();

        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // Deduct from balance
        $employee = $leave->employee;
        if ($leave->type === 'annual') {
            $employee->decrement('annual_leave_balance', $leave->days);
        } elseif ($leave->type === 'sick') {
            $employee->decrement('sick_leave_balance', $leave->days);
        }

        // Update employee status
        $employee->update(['status' => 'on_leave']);

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $this->authorizeHR();

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Leave rejected.');
    }

    public function destroy(LeaveRequest $leave)
    {
        $this->authorizeHR();

        abort_if($leave->status !== 'pending', 403, 'Can only delete pending requests.');

        $leave->delete();

        return back()->with('success', 'Leave request deleted.');
    }

    private function authorizeHR(): void
    {
        abort_if(!auth()->user()->hasRole(['admin', 'hr', 'ceo']), 403);
    }
}
