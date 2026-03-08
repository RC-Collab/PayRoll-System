# Leave Management System - Integration & API Documentation

## Overview
The Leave Management System is fully integrated with the backend and ready for production deployment. It provides comprehensive leave request, approval, and tracking functionality with real-time data binding.

## System Components

### 1. Database Schema
**Table:** `leave_records`

```
Columns:
- id (Primary Key)
- employee_id (Foreign Key → employees)
- leave_type (ENUM: sick, casual, annual, maternity, paternity, bereavement, unpaid)
- start_date (DATE)
- end_date (DATE)
- total_days (FLOAT - supports half days)
- reason (TEXT)
- contact_during_leave (VARCHAR - nullable)
- medical_certificate (BOOLEAN - default: false)
- status (ENUM: pending, approved, rejected, cancelled)
- approved_by (Foreign Key → users - nullable)
- approved_at (TIMESTAMP - nullable)
- rejected_by (Foreign Key → users - nullable)
- rejected_at (TIMESTAMP - nullable)
- remarks (TEXT - nullable)
- created_at, updated_at (TIMESTAMPS)
```

### 2. Backend Implementation

#### LeaveController Methods

**index()** - List and manage all leaves
```
Route: GET /leaves
Returns:
- stats: Object with counts (pending, approved, rejected, onLeave)
- pendingLeaves: Collection of pending leave records with relationships
- approvedLeaves: Paginated collection (15 per page) of approved leaves
- rejectedLeaves: Paginated collection of rejected leaves
- currentLeaves: Leaves currently active (date range matches today)
```

**create()** - Display leave application form
```
Route: GET /leaves/apply
Returns:
- employees: List of active employees (employment_status != 'resigned')
- Pre-populated form for leave application
```

**store(Request)** - Submit leave application
```
Route: POST /leaves/apply
Validation:
- employee_id: required|exists:employees,id
- leave_type: required|in:sick,casual,annual,maternity,paternity,bereavement,unpaid
- start_date: required|date|after_or_equal:today
- end_date: required|date|after_or_equal:start_date
- reason: required|string|min:10
- medical_certificate: nullable|boolean
- is_half_day: nullable|boolean
- half_day_period: nullable|in:first_half,second_half

Processing:
- Calculates total_days including half-day adjustments
- Creates LeaveRecord with status='pending'
- Uses transaction for data integrity
```

**checkBalance(Request)** - AJAX endpoint for leave balance verification
```
Route: GET /leaves/check-balance
Parameters:
- employee_id: ID of employee
- leave_type: Type of leave
- start_date: Start date of requested leave
- end_date: End date of requested leave

Response:
{
    "success": true,
    "leave_type": "Casual Leave",
    "available_days": 8.5,
    "requested_days": 3,
    "can_apply": true
}

Leave Allocation (default):
- sick: 12 days/year
- casual: 10 days/year
- annual: 20 days/year
- maternity: 90 days/year
- paternity: 15 days/year
- bereavement: 5 days/year
- unpaid: Unlimited
```

**approve(Request, $id)** - Approve pending leave request
```
Route: POST /leaves/{leave}/approve
Parameters:
- remarks: Optional approval remarks

Processing:
- Updates leave status to 'approved'
- Records approved_by (current user) and approved_at
- If leave has started: Updates employee employment_status to 'on-leave'
- Uses transaction for data consistency
```

**reject(Request, $id)** - Reject leave request
```
Route: POST /leaves/{leave}/reject
Parameters:
- remarks: Rejection reason (required)

Processing:
- Updates leave status to 'rejected'
- Records rejected_by, rejected_at, and remarks
- Does NOT update employee status
```

**show($id)** - View leave details
```
Route: GET /leaves/{leave}
Returns: Detailed leave record with employee and department information
```

### 3. Models

#### LeaveRecord Model
```php
class LeaveRecord extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type', 'start_date', 'end_date', 
        'total_days', 'reason', 'contact_during_leave', 
        'medical_certificate', 'status', 'approved_by', 
        'approved_at', 'rejected_by', 'rejected_at', 'remarks'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'total_days' => 'float',
        'medical_certificate' => 'boolean',
    ];

    // Relationships
    public function employee(): BelongsTo
    public function approver(): BelongsTo (User)
}
```

#### Employee Model Relationship
```php
public function leaveRecords()
{
    return $this->hasMany(LeaveRecord::class);
}
```

### 4. Frontend Implementation

#### Views
- **resources/views/leaves/index.blade.php** - Admin/HR dashboard
  - Statistics cards (Pending, Approved, Rejected, On Leave counts)
  - Pending leaves table (highest priority)
  - Tabs for Approved, Rejected, and Currently On Leave leaves
  - Action modals for approve/reject with remarks

- **resources/views/leaves/apply.blade.php** - Leave application form
  - Employee selection (for HR/Admin) or pre-selected (for employees)
  - Leave type dropdown (7 types)
  - Date range picker
  - Half-day options (for sick/casual leaves only)
  - Real-time leave balance checking via AJAX
  - Contact information fields
  - Work handover notes section

- **resources/views/leaves/_leave_list.blade.php** - Reusable leave list partial
  - Employee details with avatar
  - Leave type with color-coded badges
  - Date range display
  - Days calculation
  - Reason (truncated with tooltip)
  - Status badges
  - Action buttons (if showActions = true)

#### JavaScript Features
1. **Date Validation**
   - Minimum date set to today
   - End date cannot be before start date
   - Auto-update end date if start date changes

2. **Half-Day Logic**
   - Only shown for sick and casual leaves
   - Adjusts total days calculation (0.5 day reduction)
   - Supports first/second half selection

3. **Real-time Balance Check**
   - Fetches via AJAX to /leaves/check-balance
   - Shows alert if insufficient balance
   - Updates on leave type, start date, or end date change

4. **Approval/Rejection Modal**
   - Opens modal for remarks input
   - Dynamic form action based on action type
   - CSRF token protection

### 5. Helper Functions

```php
// app/Helpers/functions.php

leave_type_color($type)        // Returns Bootstrap color class (danger, info, success, etc.)
leave_type_name($type)         // Returns human-readable name
leave_status_color($status)    // Returns color for status badge
format_leave_days($days)       // Formats days with .5 support (e.g., "3.5 days")

// app/Helpers/LeaveHelper.php
LeaveHelper::getLeaveTypeColor($type)
LeaveHelper::getLeaveTypeName($type)
LeaveHelper::getStatusColor($status)
LeaveHelper::formatLeaveDays($days)
```

### 6. Route Structure

```
# Leave Management Routes (Protected: role:admin,hr)
GET    /leaves                 - LeaveController@index      [List all leaves with stats]
GET    /leaves/apply           - LeaveController@create     [Show application form]
POST   /leaves/apply           - LeaveController@store      [Submit application]
GET    /leaves/check-balance   - LeaveController@checkBalance [AJAX balance check]
POST   /leaves/{leave}/approve - LeaveController@approve    [Approve leave]
POST   /leaves/{leave}/reject  - LeaveController@reject     [Reject leave]
GET    /leaves/{leave}         - LeaveController@show       [View details]
```

### 7. Database Migrations

**2026_01_27_add_missing_columns_to_leave_records.php** - Adds:
- medical_certificate (boolean)
- rejected_by (foreign key)
- rejected_at (timestamp)
- remarks (text)

**2026_01_27_fix_leave_types_enum.php** - Updates enum values:
- From: sick_leave, casual_leave, annual_leave, etc.
- To: sick, casual, annual, maternity, paternity, bereavement, unpaid

## API Integration Points

### For Mobile/Frontend Apps

All endpoints return JSON responses with standard format:

```json
{
    "status": "success",
    "message": "Leave approved successfully!",
    "data": {
        "id": 1,
        "employee_id": 5,
        "leave_type": "casual",
        "start_date": "2026-02-01",
        "end_date": "2026-02-03",
        "total_days": 3,
        "status": "approved",
        "approved_at": "2026-01-27 14:30:00"
    }
}
```

### Recommended API Endpoints (REST Style)

```
# GET endpoints (read-only)
GET /api/leaves                    - List all leaves with pagination
GET /api/leaves?status=pending     - Filter by status
GET /api/leaves?employee_id=5      - Filter by employee
GET /api/leaves/{id}              - Get leave details
GET /api/leaves/{id}/balance      - Check employee leave balance

# POST endpoints (create/update)
POST /api/leaves                  - Create new leave request
POST /api/leaves/{id}/approve     - Approve leave
POST /api/leaves/{id}/reject      - Reject leave
POST /api/leaves/{id}/cancel      - Cancel approved leave
```

## Business Logic & Validation

### Leave Processing Workflow

1. **Application Phase**
   - Employee submits leave request (status: pending)
   - System calculates total days
   - Balance check performed (warning if insufficient)

2. **Approval Phase**
   - Admin/HR reviews pending leaves
   - Optionally adds remarks
   - Updates leave status to 'approved'
   - If leave has started: employee status → 'on-leave'

3. **Rejection Phase**
   - Rejection reason recorded
   - Employee notified of rejection
   - No status changes to employee

4. **Active Leave Phase**
   - Automatically tracked via date range
   - "Currently on Leave" section shows active leaves
   - Days remaining calculated

### Important Validations

✅ **Implemented:**
- Start date cannot be in the past
- End date must be >= Start date
- Reason minimum 10 characters
- Half-day only for certain leave types
- Employee must not be resigned
- Date format validation (YYYY-MM-DD)

⚠️ **Recommended to Add:**
- Leave balance enforcement (prevent over-applying)
- Overlapping leave detection
- Minimum notice period validation (if policy-based)
- Maximum consecutive days limit
- Holiday/weekend exclusion in day calculation
- Department-specific leave policies

## Performance Optimization

### Eager Loading
All queries use `.with('employee', 'employee.departments')` to prevent N+1 queries.

### Pagination
- Approved/Rejected leaves: 15 per page
- Pending leaves: No pagination (listed all for admin attention)

### Caching Recommendations
```php
// Cache leave balance check
Cache::remember("leave_balance:{$employeeId}:{$leaveType}", 3600, function() {
    // Calculate balance
});
```

## Testing Recommendations

### Test Data
1. Create test employees with different statuses
2. Submit leave requests with various scenarios:
   - Half-day leaves
   - Overlapping leaves
   - Insufficient balance
   - Invalid date ranges
3. Test approval/rejection with remarks
4. Verify employee status changes to 'on-leave'

### Edge Cases
- Leap year date calculations
- Month-end leaves (Jan 28 - Feb 2)
- Single-day leaves vs multi-day
- Half-day on single day vs multi-day period
- Concurrent leave approvals

## Known Limitations & Future Enhancements

### Current Limitations
1. Leave balance is hardcoded (should be database-configurable)
2. No weekend/holiday exclusion in day calculation
3. Cannot revoke approved leaves
4. No email notifications integrated
5. No leave carry-over logic
6. No entitlement policy engine

### Recommended Enhancements
1. **LeaveBalance Table**
   - Track remaining leave days per employee per type per year
   - Auto-adjust on approval/rejection

2. **LeavePolicy Table**
   - Department-specific leave policies
   - Role-based entitlements
   - Notice period requirements

3. **Email Notifications**
   - Send to employee on approval/rejection
   - Send to manager when leave starts
   - Auto-reminders before return date

4. **Reporting**
   - Annual leave statistics
   - Department-wise leave trends
   - Employee leave history

5. **Calendar View**
   - Visual leave calendar
   - Overlap detection
   - Coverage planning

## Migration & Deployment Steps

```bash
# 1. Add new migrations
php artisan migrate

# 2. Verify database schema
php artisan tinker
>>> \App\Models\LeaveRecord::first()

# 3. Test form submission
# Visit: /leaves/apply
# Fill form and submit

# 4. Test admin approval
# Visit: /leaves
# Click approve on pending leave

# 5. Verify employee status update
# Check employee record for 'on-leave' status

# 6. Clear caches if used
php artisan cache:clear
php artisan config:clear
```

## Security Features

✅ **Implemented:**
- Role-based access control (admin,hr middleware)
- CSRF token protection on forms
- Database transaction for data consistency
- Input validation on all fields
- Foreign key constraints

✅ **Recommended:**
- Rate limiting on leave submission
- Audit log for approvals/rejections
- Approval chain (if organization requires)
- Encryption for sensitive data
- API key authentication for mobile apps

## Troubleshooting

### Common Issues

1. **"Field 'leave_type' doesn't have default value"**
   - Run: `php artisan migrate --path="database/migrations/2026_01_27_fix_leave_types_enum.php"`

2. **Leave balance not checking**
   - Verify `/leaves/check-balance` route is accessible
   - Check browser console for AJAX errors
   - Ensure employee_id is being passed

3. **Employee status not updating to 'on-leave'**
   - Verify employee has `employment_status` column
   - Check if approval is for future date (only updates if >= today)
   - Run: `php artisan tinker` and check employee record

4. **Form validation errors**
   - Ensure StoreLeaveRequest is imported in LeaveController
   - Check request class for message customization

## Support & Maintenance

For issues or feature requests:
1. Check migration logs: `php artisan migrate:status`
2. Test with minimal data
3. Check storage logs: `storage/logs/laravel.log`
4. Verify database connections

---
**Status:** Production Ready ✅
**Last Updated:** 2026-01-27
**Version:** 1.0
