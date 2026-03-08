<?php

namespace App\Http\Controllers;

use App\Models\LeaveRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index()
    {
        // Statistics
        $stats = [
            'pending' => LeaveRecord::where('status', 'pending')->count(),
            'approved' => LeaveRecord::where('status', 'approved')->count(),
            'rejected' => LeaveRecord::where('status', 'rejected')->count(),
            'onLeave' => Employee::where('employment_status', 'on-leave')->count(),
        ];

        // Pending leaves (top priority for admin)
        $pendingLeaves = LeaveRecord::with('employee.departments')
            ->where('status', 'pending')
            ->latest()
            ->get();

        // Approved leaves
        $approvedLeaves = LeaveRecord::with('employee')
            ->where('status', 'approved')
            ->latest()
            ->paginate(15);

        // Rejected leaves
        $rejectedLeaves = LeaveRecord::with('employee')
            ->where('status', 'rejected')
            ->latest()
            ->paginate(15);

        // Currently on leave (approved and within date range)
        $currentLeaves = LeaveRecord::with('employee.departments')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest()
            ->get();

        return view('leaves.index', compact(
            'stats', 
            'pendingLeaves', 
            'approvedLeaves', 
            'rejectedLeaves', 
            'currentLeaves'
        ));
    }

    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $leave = LeaveRecord::with('employee')->findOrFail($id);
            
            $leave->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $request->remarks,
            ]);

            // Update employee status to "on-leave" if leave starts today or earlier
            if (now()->greaterThanOrEqualTo($leave->start_date)) {
                $leave->employee->update([
                    'employment_status' => 'on-leave'
                ]);
            }

            DB::commit();

            return redirect()->route('leaves.index')
                ->with('success', 'Leave approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('leaves.index')
                ->with('error', 'Failed to approve leave: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $leave = LeaveRecord::findOrFail($id);
        
        $leave->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave rejected successfully!');
    }

    // Display create form
    public function create()
    {
        $employees = Employee::where('employment_status', '!=', 'resigned')
            ->orderBy('first_name')
            ->get();

        return view('leaves.apply', compact('employees'));
    }

    // Store new leave request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:sick,casual,annual,maternity,paternity,bereavement,unpaid',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'contact_during_leave' => 'nullable|string|max:20',
            'contact_number' => 'nullable|string|max:20',
            'alternate_contact' => 'nullable|string|max:100',
            'handover_notes' => 'nullable|string',
            'medical_certificate' => 'nullable|boolean',
            'is_half_day' => 'nullable|boolean',
            'half_day_period' => 'nullable|in:first_half,second_half',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total days
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date']);
            $totalDays = $endDate->diffInDays($startDate) + 1;
            
            // Adjust for half day
            if ($validated['is_half_day'] ?? false) {
                $totalDays = $totalDays - 0.5;
            }

            // Create leave record
            $leave = LeaveRecord::create([
                'employee_id' => $validated['employee_id'],
                'leave_type' => $validated['leave_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $totalDays,
                'reason' => $validated['reason'],
                'contact_during_leave' => $validated['contact_during_leave'],
                'medical_certificate' => $validated['medical_certificate'] ?? false,
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('leaves.index')
                ->with('success', 'Leave application submitted successfully! It is now pending approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit leave application: ' . $e->getMessage());
        }
    }

    // Check leave balance (AJAX endpoint)
    public function checkBalance(Request $request)
    {
        $employeeId = $request->employee_id;
        $leaveType = $request->leave_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if (!$employeeId || !$leaveType || !$startDate || !$endDate) {
            return response()->json(['success' => false], 400);
        }

        try {
            // Calculate requested days
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            $requestedDays = $end->diffInDays($start) + 1;

            // Get available leave days (from a LeaveBalance table or calculate)
            // For now, we'll use a default allocation - adjust based on your business logic
            $leaveAllocation = [
                'sick' => 12,
                'casual' => 10,
                'annual' => 20,
                'maternity' => 90,
                'paternity' => 15,
                'bereavement' => 5,
                'unpaid' => 999,
            ];

            $availableDays = $leaveAllocation[$leaveType] ?? 0;

            // Count already approved days for this employee in this year
            $approvedDays = LeaveRecord::where('employee_id', $employeeId)
                ->where('leave_type', $leaveType)
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('total_days');

            $remainingDays = $availableDays - $approvedDays;
            $canApply = $remainingDays >= $requestedDays;

            return response()->json([
                'success' => true,
                'leave_type' => leave_type_name($leaveType),
                'available_days' => round($remainingDays, 1),
                'requested_days' => $requestedDays,
                'can_apply' => $canApply,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking balance: ' . $e->getMessage()
            ], 500);
        }
    }

    // Optional: View leave details
    public function show($id)
    {
        $leave = LeaveRecord::with(['employee', 'employee.departments'])->findOrFail($id);
        return view('leaves.show', compact('leave'));
    }
}