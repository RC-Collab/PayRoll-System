# Android App REST API Documentation

## Base URL
```
https://your-domain.com/api
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## 1️⃣ AUTHENTICATION APIS

### POST /api/login
Login with email and password to get access token.

**Request:**
```json
{
  "email": "employee@example.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "message": "Login successful",
  "access_token": "8|I8xyz9abcdefghijklmnopqrstuvwxyz123456789",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "roles": ["employee"]
  }
}
```

**Error Response (422):**
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

### POST /api/logout
Logout and revoke the current access token.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Logout successful"
}
```

---

### GET /api/profile
Get the current authenticated user's profile.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Profile retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "roles": ["employee"]
  },
  "employee": {
    "id": 5,
    "employee_code": "EMP001",
    "first_name": "John",
    "middle_name": "Kumar",
    "last_name": "Doe",
    "email": "john@example.com",
    "mobile_number": "+977-9841234567",
    "designation": "Mathematics Teacher",
    "employee_type": "permanent",
    "employment_status": "active",
    "profile_image": "https://your-domain.com/storage/employees/emp001.jpg",
    "joining_date": "2023-01-15"
  }
}
```

---

### POST /api/profile/update
Update user profile information.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "first_name": "John",
  "middle_name": "Kumar",
  "last_name": "Doe",
  "mobile_number": "+977-9841234567",
  "current_password": "password123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Success Response (200):**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "roles": ["employee"]
  }
}
```

---

## 2️⃣ ATTENDANCE APIS

### POST /api/attendance/mark
Mark attendance for one or multiple dates.

**Headers:**
```
Authorization: Bearer {token}
```

**Request - Teacher (Period-wise):**
```json
{
  "dates": ["2024-02-21", "2024-02-22"],
  "attendance_type": "period_wise",
  "periods": ["P", "P", "A", "P", "P", "P", "A", "P"],
  "remarks": "Attended all classes except period 3 and 7"
}
```

**Request - Staff (Day-wise):**
```json
{
  "dates": ["2024-02-21"],
  "attendance_type": "full_day",
  "status": "present",
  "remarks": "Present at work"
}
```

**Success Response (201):**
```json
{
  "message": "Attendance marked successfully",
  "data": [
    {
      "id": 1,
      "date": "2024-02-21",
      "total_periods": 8,
      "present_periods": 6,
      "absent_periods": 2,
      "overtime_periods": 0,
      "period_data": [
        {
          "period": 1,
          "status": "P",
          "is_overtime": false,
          "time": null,
          "notes": null
        },
        {
          "period": 2,
          "status": "P",
          "is_overtime": false,
          "time": null,
          "notes": null
        }
      ],
      "notes": "Attended all classes except period 3 and 7"
    }
  ]
}
```

---

### GET /api/attendance/today
Get today's attendance record.

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Today's attendance",
  "data": {
    "id": 15,
    "date": "2024-02-21",
    "total_periods": 8,
    "present_periods": 7,
    "absent_periods": 1,
    "overtime_periods": 0,
    "period_data": [
      {
        "period": 1,
        "status": "P",
        "is_overtime": false
      },
      {
        "period": 2,
        "status": "P",
        "is_overtime": false
      },
      {
        "period": 3,
        "status": "A",
        "is_overtime": false
      }
    ],
    "notes": null
  }
}
```

---

### GET /api/attendance/monthly
Get monthly attendance with filters.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `month` (optional, 1-12): Default is current month
- `year` (optional, YYYY): Default is current year

**Example URL:**
```
GET /api/attendance/monthly?month=2&year=2024
```

**Success Response (200) - Teacher:**
```json
{
  "message": "Monthly attendance retrieved",
  "month": 2,
  "year": 2024,
  "data": [
    {
      "id": 1,
      "date": "2024-02-01",
      "total_periods": 8,
      "present_periods": 8,
      "absent_periods": 0,
      "overtime_periods": 0,
      "period_data": []
    }
  ],
  "summary": {
    "total_days": 20,
    "total_periods": 160,
    "present_periods": 145,
    "absent_periods": 15
  }
}
```

**Success Response (200) - Staff:**
```json
{
  "message": "Monthly attendance retrieved",
  "month": 2,
  "year": 2024,
  "data": [
    {
      "id": 1,
      "date": "2024-02-01",
      "check_in": "09:00:30",
      "check_out": "17:30:45",
      "status": "present",
      "total_hours": 8.5,
      "is_late": false,
      "late_minutes": 0,
      "overtime_minutes": 30,
      "overtime_hours": 0.5,
      "regular_hours": 7.5,
      "notes": null
    }
  ],
  "summary": {
    "total_days": 20,
    "present": 18,
    "absent": 1,
    "half_day": 1,
    "total_hours": 160.5
  }
}
```

> **Note:** Overtime and late calculations respect each employee’s configured start/end times and apply a 15‑minute rounding rule. Times within 0‑15 minutes of the hour round down, 45‑60 minutes round up, all other times snap to the half‑hour.

---

### GET /api/attendance/yearly
Get yearly attendance with monthly breakdown.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `year` (optional, YYYY): Default is current year

**Example URL:**
```
GET /api/attendance/yearly?year=2024
```

**Success Response (200) - Teacher:**
```json
{
  "message": "Yearly attendance retrieved",
  "year": 2024,
  "monthly_data": [
    {
      "month": 1,
      "total_days": 22,
      "present_periods": 165,
      "absent_periods": 11
    },
    {
      "month": 2,
      "total_days": 20,
      "present_periods": 145,
      "absent_periods": 15
    }
  ],
  "summary": {
    "total_working_days": 242,
    "total_present_periods": 1936,
    "total_absent_periods": 96
  }
}
```

**Success Response (200) - Staff:**
```json
{
  "message": "Yearly attendance retrieved",
  "year": 2024,
  "monthly_data": [
    {
      "month": 1,
      "total_days": 22,
      "present": 20,
      "absent": 1,
      "half_day": 1
    },
    {
      "month": 2,
      "total_days": 20,
      "present": 18,
      "absent": 1,
      "half_day": 1
    }
  ],
  "summary": {
    "total_working_days": 242,
    "total_present": 220,
    "total_absent": 15,
    "total_half_day": 7
  }
}
```

---

## 3️⃣ LEAVE APIS

### POST /api/leave/apply
Apply for leave.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "leave_type": "casual_leave",
  "start_date": "2024-03-01",
  "end_date": "2024-03-05",
  "total_days": 5,
  "reason": "Personal emergency",
  "contact_during_leave": "+977-9841234567",
  "medical_certificate": false
}
```

**Allowed Leave Types:**
- `sick_leave`
- `casual_leave`
- `annual_leave`
- `maternity_leave`
- `paternity_leave`
- `study_leave`
- `bereavement_leave`
- `unpaid_leave`

**Success Response (201):**
```json
{
  "message": "Leave application submitted successfully",
  "data": {
    "id": 1,
    "leave_type": "casual_leave",
    "start_date": "2024-03-01",
    "end_date": "2024-03-05",
    "total_days": 5,
    "reason": "Personal emergency",
    "status": "pending",
    "contact_during_leave": "+977-9841234567",
    "medical_certificate": false,
    "approved_by": null,
    "approved_at": null,
    "approval_remarks": null,
    "rejected_by": null,
    "rejected_at": null,
    "remarks": null,
    "created_at": "2024-02-21 10:30:00"
  }
}
```

**Error Response (422) - Overlapping Leave:**
```json
{
  "message": "You already have a leave application for these dates",
  "status": 422
}
```

---

### GET /api/leave/list
Get leave applications.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): `pending`, `approved`, `rejected`, `cancelled`
- `year` (optional, YYYY): Default is current year

**Example URL:**
```
GET /api/leave/list?status=pending&year=2024
```

**Success Response (200):**
```json
{
  "message": "Leave applications retrieved successfully",
  "year": 2024,
  "status_filter": "pending",
  "data": [
    {
      "id": 1,
      "leave_type": "casual_leave",
      "start_date": "2024-03-01",
      "end_date": "2024-03-05",
      "total_days": 5,
      "reason": "Personal emergency",
      "status": "pending",
      "contact_during_leave": "+977-9841234567",
      "medical_certificate": false,
      "approved_by": null,
      "approved_at": null,
      "approval_remarks": null,
      "rejected_by": null,
      "rejected_at": null,
      "remarks": null,
      "created_at": "2024-02-21 10:30:00"
    }
  ],
  "summary": {
    "total": 5,
    "pending": 3,
    "approved": 1,
    "rejected": 1,
    "cancelled": 0
  }
}
```

---

### POST /api/leave/cancel
Cancel a leave application.

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "leave_id": 1,
  "reason": "No longer needed"
}
```

**Success Response (200):**
```json
{
  "message": "Leave cancelled successfully",
  "data": {
    "id": 1,
    "leave_type": "casual_leave",
    "start_date": "2024-03-01",
    "end_date": "2024-03-05",
    "total_days": 5,
    "reason": "Personal emergency",
    "status": "cancelled",
    "contact_during_leave": "+977-9841234567",
    "medical_certificate": false,
    "approved_by": null,
    "approved_at": null,
    "approval_remarks": null,
    "rejected_by": null,
    "rejected_at": null,
    "remarks": "No longer needed",
    "created_at": "2024-02-21 10:30:00"
  }
}
```

**Error Response (422):**
```json
{
  "message": "Cannot cancel leaves that have already started",
  "status": 422
}
```

---

## 4️⃣ SALARY APIS

### GET /api/salary/monthly
Get salary for a specific month.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `month` (required, 1-12)
- `year` (required, YYYY)

**Example URL:**
```
GET /api/salary/monthly?month=2&year=2024
```

**Success Response (200):**
```json
{
  "message": "Monthly salary retrieved successfully",
  "data": {
    "id": 2,
    "salary_month": "2024-02",
    "working_days": 20,
    "present_days": 18,
    "absent_days": 1,
    "leave_days": 1,
    "basic_salary": 25000.00,
    "total_allowances": 8500.00,
    "total_deductions": 5500.00,
    "gross_salary": 33500.00,
    "net_salary": 28000.00,
    "payment_status": "paid",
    "payment_date": "2024-02-28",
    "payment_method": "bank_transfer",
    "payment_bank": "Nabil Bank",
    "cheque_number": "CHQ0001",
    "paid_amount": 28000.00,
    "transaction_reference": "TXN123456789",
    "remarks": "Salary processed successfully"
  }
}
```

---

### GET /api/salary/status
Get salary payment status for the year.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `year` (optional, YYYY): Default is current year

**Example URL:**
```
GET /api/salary/status?year=2024
```

**Success Response (200):**
```json
{
  "message": "Salary payment status retrieved successfully",
  "year": 2024,
  "data": [
    {
      "month": "2024-01",
      "month_name": "January 2024",
      "net_salary": 28000.00,
      "payment_status": "paid",
      "payment_date": "2024-01-31",
      "payment_method": "bank_transfer",
      "payment_bank": "Nabil Bank",
      "cheque_number": "CHQ0000",
      "paid_amount": 28000.00,
      "transaction_reference": "TXN001234567"
    },
    {
      "month": "2024-02",
      "month_name": "February 2024",
      "net_salary": 28000.00,
      "payment_status": "pending",
      "payment_date": null,
      "payment_method": null,
      "transaction_reference": null
    }
  ],
  "summary": {
    "total_months": 12,
    "paid": 1,
    "pending": 11,
    "total_amount": 336000.00
  }
}
```

---

### GET /api/salary/receipt/{id}
Get detailed salary slip/receipt.

**Headers:**
```
Authorization: Bearer {token}
```

**Example URL:**
```
GET /api/salary/receipt/2
```

**Success Response (200):**
```json
{
  "message": "Salary receipt retrieved successfully",
  "data": {
    "employee": {
      "id": 5,
      "employee_code": "EMP001",
      "name": "John Kumar Doe",
      "email": "john@example.com",
      "designation": "Mathematics Teacher",
      "bank_account": "9876543210"
    },
    "salary_month": "February 2024",
    "earnings": {
      "basic_salary": 25000.00,
      "dearness_allowance": 2500.00,
      "house_rent_allowance": 3000.00,
      "medical_allowance": 1000.00,
      "tiffin_allowance": 500.00,
      "transport_allowance": 1000.00,
      "special_allowance": 500.00,
      "overtime_amount": 0.00,
      "bonus_amount": 0.00
    },
    "deductions": {
      "provident_fund": 2500.00,
      "citizen_investment": 1000.00,
      "income_tax": 1500.00,
      "insurance_amount": 300.00,
      "late_deduction": 0.00,
      "absent_deduction": 200.00,
      "advance_deduction": 0.00,
      "penalty_deduction": 0.00
    },
    "totals": {
      "total_allowances": 8500.00,
      "total_deductions": 5500.00,
      "gross_salary": 33500.00,
      "net_salary": 28000.00
    },
    "attendance": {
      "working_days": 20,
      "present_days": 18,
      "absent_days": 1,
      "leave_days": 1,
      "overtime_hours": 0
    },
    "payment": {
      "status": "paid",
      "date": "2024-02-28",
      "method": "bank_transfer",
      "transaction_ref": "TXN123456789"
    }
  }
}
```

---

## 5️⃣ REPORT APIS

### GET /api/report/monthly
Get detailed monthly attendance and leave report.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `month` (required, 1-12)
- `year` (required, YYYY)

**Example URL:**
```
GET /api/report/monthly?month=2&year=2024
```

**Success Response (200) - Teacher:**
```json
{
  "message": "Monthly report retrieved successfully",
  "month": 2,
  "year": 2024,
  "month_name": "February 2024",
  "attendance_summary": {
    "type": "period_wise",
    "total_days": 20,
    "total_periods": 160,
    "present_periods": 145,
    "absent_periods": 15,
    "overtime_periods": 0
  },
  "leave_summary": {
    "total": 1,
    "approved": 1,
    "pending": 0,
    "rejected": 0,
    "cancelled": 0,
    "total_leave_days": 1,
    "by_type": [
      {
        "type": "casual_leave",
        "count": 1,
        "days": 1
      }
    ]
  },
  "daily_report": [
    {
      "date": "2024-02-01",
      "day": "Thursday",
      "attendance": {
        "present_periods": 8,
        "absent_periods": 0,
        "total_periods": 8
      }
    },
    {
      "date": "2024-02-02",
      "day": "Friday",
      "attendance": null,
      "leaves": [
        {
          "type": "casual_leave",
          "total_days": 1
        }
      ]
    }
  ]
}
```

**Success Response (200) - Staff:**
```json
{
  "message": "Monthly report retrieved successfully",
  "month": 2,
  "year": 2024,
  "month_name": "February 2024",
  "attendance_summary": {
    "type": "day_wise",
    "total_days": 20,
    "present": 18,
    "absent": 1,
    "half_day": 1,
    "total_hours": 160.5
  },
  "leave_summary": {
    "total": 1,
    "approved": 1,
    "pending": 0,
    "rejected": 0,
    "cancelled": 0,
    "total_leave_days": 1,
    "by_type": [
      {
        "type": "casual_leave",
        "count": 1,
        "days": 1
      }
    ]
  },
  "daily_report": [
    {
      "date": "2024-02-01",
      "day": "Thursday",
      "attendance": {
        "status": "present",
        "check_in": "09:00:30",
        "check_out": "17:30:45",
        "total_hours": 8.5
      }
    }
  ]
}
```

---

### GET /api/report/yearly
Get yearly attendance and leave summary with monthly breakdown.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `year` (required, YYYY)

**Example URL:**
```
GET /api/report/yearly?year=2024
```

**Success Response (200) - Teacher:**
```json
{
  "message": "Yearly report retrieved successfully",
  "year": 2024,
  "summary": {
    "type": "period_wise",
    "total_working_days": 242,
    "total_periods": 1936,
    "present_periods": 1820,
    "absent_periods": 116,
    "overtime_periods": 0,
    "total_leave_days": 5,
    "total_leaves": 2
  },
  "monthly_breakdown": [
    {
      "month": 1,
      "month_name": "January",
      "attendance": {
        "total_days": 22,
        "total_periods": 176,
        "present_periods": 165,
        "absent_periods": 11
      },
      "leaves": {
        "total_days": 0,
        "count": 0
      }
    },
    {
      "month": 2,
      "month_name": "February",
      "attendance": {
        "total_days": 20,
        "total_periods": 160,
        "present_periods": 145,
        "absent_periods": 15
      },
      "leaves": {
        "total_days": 1,
        "count": 1
      }
    }
  ]
}
```

**Success Response (200) - Staff:**
```json
{
  "message": "Yearly report retrieved successfully",
  "year": 2024,
  "summary": {
    "type": "day_wise",
    "total_working_days": 242,
    "total_present": 220,
    "total_absent": 15,
    "total_half_day": 7,
    "total_hours": 1936.5,
    "total_leave_days": 5,
    "total_leaves": 2
  },
  "monthly_breakdown": [
    {
      "month": 1,
      "month_name": "January",
      "attendance": {
        "total_days": 22,
        "present": 20,
        "absent": 1,
        "half_day": 1
      },
      "leaves": {
        "total_days": 0,
        "count": 0
      }
    },
    {
      "month": 2,
      "month_name": "February",
      "attendance": {
        "total_days": 20,
        "present": 18,
        "absent": 1,
        "half_day": 1
      },
      "leaves": {
        "total_days": 1,
        "count": 1
      }
    }
  ]
}
```

---

## Error Handling

### Common Error Responses

**401 Unauthorized (Missing or Invalid Token):**
```json
{
  "message": "Unauthenticated"
}
```

**403 Forbidden (Access Denied):**
```json
{
  "message": "Unauthorized access"
}
```

**404 Not Found:**
```json
{
  "message": "Resource not found"
}
```

**422 Validation Error:**
```json
{
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**500 Internal Server Error:**
```json
{
  "message": "Failed to process request",
  "error": "Error details"
}
```

---

## Implementation Notes

### Sanctum Configuration
- All tokens are stored in the `personal_access_tokens` table
- Tokens expire based on your `.env` configuration
- Use Bearer token authentication for all protected endpoints

### Teacher vs Staff Differentiation
- **Teachers**: `employee_type = 'teacher'` OR `designation = 'teacher'`
- **Staff**: All other employees
- Teachers use period-wise attendance (P1-P12)
- Staff use day-wise attendance (present, absent, half_day)

### Multiple Date Rules
- **Teachers**: Multiple dates are treated as full day, periods are ignored
- **Staff**: Multiple dates are always treated as full day

### Migrations Required
```bash
php artisan migrate
```

This will run the migration to add `user_id` foreign key to the `employees` table.

### Setup Instructions

1. **Run Migration:**
   ```bash
   php artisan migrate
   ```

2. **Link Users to Employees:**
   Make sure each employee has a corresponding user_id in the employees table.

3. **Test API:**
   Use Postman or Insomnia to test the endpoints.

4. **Authentication Token:**
   Token format: `{number}|{token_string}`
   Example: `8|I8xyz9abcdefghijklmnopqrstuvwxyz123456789`

---

## Rate Limiting
(If configured in your project)
- Requests are limited per user/IP
- Check `X-RateLimit-*` headers in response

## CORS Configuration
- Ensure CORS is configured in your `config/cors.php` to allow requests from your Android app domain

---

**Version:** 1.0
**Last Updated:** February 21, 2024
