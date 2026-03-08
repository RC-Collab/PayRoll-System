<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApplyLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Models\LeaveRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeaveApiController extends Controller
{
    /**
     * POST /api/leave/apply
     * Apply for leave
     */
    public function apply(ApplyLeaveRequest $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $validated = $request->validated();
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $isTeacher = $employee->employee_type === 'teacher' || $employee->designation === 'teacher';

            // Check if multiple dates for teachers
            $dateDiff = $startDate->diffInDays($endDate);
            if ($isTeacher && $dateDiff > 0) {
                // Multiple dates - force full_day
                // Leave type should be full_day, already default
            } else if (!$isTeacher && $dateDiff > 0) {
                // Staff with multiple dates - force full_day
            }

            // Check for overlapping leaves
            $overlappingLeaves = LeaveRecord::where('employee_id', $employee->id)
                ->where('status', '!=', 'rejected')
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($overlappingLeaves) {
                return response()->json([
                    'message' => 'You already have a leave application for these dates',
                ], 422);
            }

            $leaveRecord = LeaveRecord::create([
                'employee_id' => $employee->id,
                'leave_type' => $validated['leave_type'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $validated['total_days'],
                'reason' => $validated['reason'],
                'contact_during_leave' => $validated['contact_during_leave'] ?? null,
                'medical_certificate' => $validated['medical_certificate'] ?? false,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Leave application submitted successfully',
                'data' => new LeaveResource($leaveRecord),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to apply leave',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/leave/list
     * Get leave applications
     */
    public function list(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $status = $request->query('status'); // pending, approved, rejected, cancelled
            $year = $request->query('year', now()->year);

            $query = LeaveRecord::where('employee_id', $employee->id)
                ->whereYear('start_date', $year);

            if ($status && in_array($status, ['pending', 'approved', 'rejected', 'cancelled'])) {
                $query->where('status', $status);
            }

            $leaves = $query->orderBy('start_date', 'desc')->get();

            return response()->json([
                'message' => 'Leave applications retrieved successfully',
                'year' => $year,
                'status_filter' => $status,
                'data' => LeaveResource::collection($leaves),
                'summary' => [
                    'total' => $leaves->count(),
                    'pending' => $leaves->where('status', 'pending')->count(),
                    'approved' => $leaves->where('status', 'approved')->count(),
                    'rejected' => $leaves->where('status', 'rejected')->count(),
                    'cancelled' => $leaves->where('status', 'cancelled')->count(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve leave applications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/leave/cancel
     * Cancel leave application
     */
    public function cancel(Request $request)
    {
        try {
            $validated = $request->validate([
                'leave_id' => 'required|exists:leave_records,id',
                'reason' => 'nullable|string|max:500',
            ]);

            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $leave = LeaveRecord::find($validated['leave_id']);

            // Check ownership
            if ($leave->employee_id !== $employee->id) {
                return response()->json([
                    'message' => 'Unauthorized access',
                ], 403);
            }

            // Check if already cancelled/rejected
            if (in_array($leave->status, ['cancelled', 'rejected'])) {
                return response()->json([
                    'message' => 'Cannot cancel a ' . $leave->status . ' leave application',
                ], 422);
            }

            // Check if leave date has passed
            if (Carbon::parse($leave->start_date)->isPast()) {
                return response()->json([
                    'message' => 'Cannot cancel leaves that have already started',
                ], 422);
            }

            $leave->update([
                'status' => 'cancelled',
                'remarks' => $validated['reason'] ?? $leave->remarks,
            ]);

            return response()->json([
                'message' => 'Leave cancelled successfully',
                'data' => new LeaveResource($leave),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel leave',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
