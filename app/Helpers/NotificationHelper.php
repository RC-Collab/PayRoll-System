<?php

/**
 * Notification Helper Functions
 * 
 * These helper functions make it easy to send notifications from anywhere in the application
 * Add this to app/Helpers/NotificationHelper.php
 */

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    /**
     * Send notification to a user
     */
    public static function notify($userId, $title, $message, $type = 'general', $relatedModel = null, $relatedId = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'related_model' => $relatedModel,
            'related_id' => $relatedId,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Send notification to multiple users
     */
    public static function notifyBatch($userIds, $title, $message, $type = 'general', $relatedModel = null, $relatedId = null)
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = self::notify($userId, $title, $message, $type, $relatedModel, $relatedId);
        }
        return $notifications;
    }

    /**
     * Send notification to all users of a role
     */
    public static function notifyRole($role, $title, $message, $type = 'general', $relatedModel = null, $relatedId = null)
    {
        $users = User::where('role', $role)->pluck('id')->toArray();
        return self::notifyBatch($users, $title, $message, $type, $relatedModel, $relatedId);
    }

    /**
     * Send notification to all admins
     */
    public static function notifyAdmins($title, $message, $type = 'general', $relatedModel = null, $relatedId = null)
    {
        return self::notifyRole('admin', $title, $message, $type, $relatedModel, $relatedId);
    }

    /**
     * Send notification to all HR users
     */
    public static function notifyHR($title, $message, $type = 'general', $relatedModel = null, $relatedId = null)
    {
        return self::notifyRole('hr', $title, $message, $type, $relatedModel, $relatedId);
    }

    /**
     * Send notification to employee about salary
     */
    public static function notifySalaryProcessed($employeeId, $month, $year, $salaryId = null)
    {
        return self::notify(
            $employeeId,
            'Salary Processed - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)),
            "Your salary for " . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . " has been processed. You can download your salary slip from the Salary section.",
            'salary',
            'MonthlySalary',
            $salaryId
        );
    }

    /**
     * Send notification to employee about leave approval
     */
    public static function notifyLeaveApproved($employeeId, $leaveId, $leaveType, $duration)
    {
        return self::notify(
            $employeeId,
            'Leave Request Approved',
            "Your " . ucfirst(str_replace('_', ' ', $leaveType)) . " leave request for " . $duration . " day(s) has been approved.",
            'leave',
            'LeaveRecord',
            $leaveId
        );
    }

    /**
     * Send notification to employee about leave rejection
     */
    public static function notifyLeaveRejected($employeeId, $leaveId, $leaveType, $reason = null)
    {
        $message = "Your " . ucfirst(str_replace('_', ' ', $leaveType)) . " leave request has been rejected.";
        if ($reason) {
            $message .= " Reason: " . $reason;
        }

        return self::notify(
            $employeeId,
            'Leave Request Rejected',
            $message,
            'leave',
            'LeaveRecord',
            $leaveId
        );
    }

    /**
     * Send notification to HR about new leave request
     */
    public static function notifyLeaveRequest($employeeId, $employeeName, $leaveId, $leaveType, $duration)
    {
        $users = User::whereIn('role', ['admin', 'hr'])->pluck('id')->toArray();
        
        return self::notifyBatch(
            $users,
            'New Leave Request - ' . $employeeName,
            $employeeName . " has requested " . $duration . " day(s) of " . str_replace('_', ' ', $leaveType) . " leave.",
            'leave',
            'LeaveRecord',
            $leaveId
        );
    }

    /**
     * Send notification to employee about attendance
     */
    public static function notifyAttendanceRecorded($employeeId, $date, $status)
    {
        return self::notify(
            $employeeId,
            'Attendance Recorded',
            "Your attendance for " . $date . " has been recorded as " . ucfirst($status) . ".",
            'attendance'
        );
    }

    /**
     * Send notification about employee creation
     */
    public static function notifyEmployeeCreated($employeeId, $employeeName, $department)
    {
        $users = User::whereIn('role', ['admin', 'hr'])->pluck('id')->toArray();
        
        return self::notifyBatch(
            $users,
            'New Employee Added',
            $employeeName . " has been added to " . $department . " department.",
            'employee'
        );
    }

    /**
     * Send notification about department update
     */
    public static function notifyDepartmentUpdate($departmentName, $message)
    {
        $users = User::whereIn('role', ['admin', 'hr'])->pluck('id')->toArray();
        
        return self::notifyBatch(
            $users,
            'Department Update - ' . $departmentName,
            $message,
            'department'
        );
    }
}

/**
 * USAGE EXAMPLES IN CONTROLLERS
 * ==============================
 * 
 * Import the helper:
 * use App\Helpers\NotificationHelper;
 * 
 * Example 1: Notify employee when salary is processed
 * 
 *   public function calculateSalary(Request $request)
 *   {
 *       // ... salary calculation logic ...
 *       
 *       // Send notification to employee
 *       NotificationHelper::notifySalaryProcessed(
 *           $employee->user_id,
 *           $month,
 *           $year,
 *           $salary->id
 *       );
 *       
 *       return redirect()->back()->with('success', 'Salary processed');
 *   }
 * 
 * Example 2: Notify HR when leave is requested
 * 
 *   public function storeLeave(StoreLeaveRequest $request)
 *   {
 *       $leave = LeaveRecord::create($request->validated());
 *       
 *       // Notify HR and admins
 *       NotificationHelper::notifyLeaveRequest(
 *           auth()->id(),
 *           auth()->user()->name,
 *           $leave->id,
 *           $leave->leave_type,
 *           $leave->getDurationInDays()
 *       );
 *       
 *       return redirect()->back()->with('success', 'Leave request submitted');
 *   }
 * 
 * Example 3: Notify employee when leave is approved
 * 
 *   public function approveLeave($leaveId)
 *   {
 *       $leave = LeaveRecord::findOrFail($leaveId);
 *       $leave->update(['status' => 'approved']);
 *       
 *       // Notify employee
 *       NotificationHelper::notifyLeaveApproved(
 *           $leave->employee->user_id,
 *           $leave->id,
 *           $leave->leave_type,
 *           $leave->getDurationInDays()
 *       );
 *       
 *       return redirect()->back()->with('success', 'Leave approved');
 *   }
 * 
 * Example 4: Notify HR when new employee is added
 * 
 *   public function storeEmployee(StoreEmployeeRequest $request)
 *   {
 *       $employee = Employee::create($request->validated());
 *       
 *       // Notify admins and HR
 *       NotificationHelper::notifyEmployeeCreated(
 *           $employee->id,
 *           $employee->name,
 *           $employee->department->name
 *       );
 *       
 *       return redirect()->route('employees.show', $employee)->with('success', 'Employee created');
 *   }
 * 
 * Example 5: Send custom notification to specific user
 * 
 *   NotificationHelper::notify(
 *       $userId,
 *       'Important Update',
 *       'Please review the new attendance policy',
 *       'general'
 *   );
 * 
 * Example 6: Notify all HR users
 * 
 *   NotificationHelper::notifyHR(
 *       'System Maintenance',
 *       'System will be under maintenance tonight from 10 PM to 12 AM',
 *       'general'
 *   );
 * 
 * Example 7: Notify all admins
 * 
 *   NotificationHelper::notifyAdmins(
 *       'New Salary Cycle',
 *       'Salary calculation for January has been completed',
 *       'salary'
 *   );
 */
