# Android App API - Setup & Implementation Guide

## ✅ Setup Instructions

### 1. Run Migration
```bash
cd /Users/roshanchaudhary/payroll_project/payroll
php artisan migrate
```

This will add the `user_id` column to the `employees` table to establish the relationship with users.

### 2. Ensure User-Employee Relationship
Make sure your existing employees are linked to users:

```php
// Option 1: Via Tinker
php artisan tinker
$employee = Employee::first();
$user = User::where('email', $employee->email)->first();
$employee->update(['user_id' => $user->id]);
exit;
```

### 3. Verify Sanctum Configuration
Check that Sanctum is properly configured in `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s,%s',
    'localhost',
    Sanctum::currentApplicationUrlWithPort(),
))),

'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
],

'expiration' => null, // Tokens don't expire (optional: set to minutes for expiration)
```

### 4. Update .env (if needed)
```
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,yourdomain.com
SANCTUM_TOKEN_PREFIX= (leave empty or set custom prefix)
```

### 5. Clear Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🗂️ File Structure Created

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php          ✅ Authentication endpoints
│   │   ├── AttendanceApiController.php ✅ Attendance marking & retrieval
│   │   ├── LeaveApiController.php      ✅ Leave management
│   │   ├── SalaryApiController.php     ✅ Salary slip & status
│   │   └── ReportApiController.php     ✅ Monthly & yearly reports
│   ├── Requests/Api/
│   │   ├── MarkAttendanceRequest.php   ✅ Attendance validation
│   │   └── ApplyLeaveRequest.php       ✅ Leave validation
│   └── Resources/
│       ├── UserResource.php            ✅ User JSON response
│       ├── AttendanceResource.php      ✅ Attendance JSON response
│       ├── LeaveResource.php           ✅ Leave JSON response
│       ├── SalaryResource.php          ✅ Salary JSON response
│       ├── PeriodWiseAttendanceResource.php
│       └── DayWiseAttendanceResource.php
│
├── Models/
│   ├── User.php                        ✅ Added employee() relationship
│   └── Employee.php                    ✅ Added user() relationship & user_id
│
└── database/migrations/
    └── 2026_02_21_000000_add_user_id_to_employees_table.php ✅ Migration
```

---

## 📋 Routes Summary

### Base URL
```
https://your-domain.com/api
```

### Authentication Routes
```
POST   /api/login                    → Login user
POST   /api/logout                   → Logout user
GET    /api/profile                  → Get user profile
POST   /api/profile/update           → Update profile
```

### Attendance Routes
```
POST   /api/attendance/mark          → Mark attendance
GET    /api/attendance/today         → Get today's attendance
GET    /api/attendance/monthly       → Get monthly attendance
GET    /api/attendance/yearly        → Get yearly attendance
```

### Leave Routes
```
POST   /api/leave/apply              → Apply for leave
GET    /api/leave/list               → Get leave applications
POST   /api/leave/cancel             → Cancel leave
```

### Salary Routes
```
GET    /api/salary/monthly           → Get monthly salary
GET    /api/salary/status            → Get payment status
GET    /api/salary/receipt/{id}      → Get salary slip
```

### Report Routes
```
GET    /api/report/monthly           → Monthly report (attendance + leave)
GET    /api/report/yearly            → Yearly report (attendance + leave)
```

---

## 🧪 Testing with Postman/Insomnia

### 1. Create Login Request
```
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "employee@example.com",
  "password": "password123"
}
```

Copy the `access_token` from the response.

### 2. Use Bearer Token
In Postman/Insomnia, add to headers:
```
Authorization: Bearer {token_from_login_response}
```

### 3. Test Endpoints
```
GET http://localhost:8000/api/profile
GET http://localhost:8000/api/attendance/today
GET http://localhost:8000/api/salary/monthly?month=2&year=2024
```

---

## 🔐 Security Considerations

### 1. CORS Configuration
Ensure CORS allows requests from your Android app:

**config/cors.php:**
```php
'allowed_origins' => ['*'], // or specify your app domain
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

### 2. HTTPS
Always use HTTPS in production:
```
https://your-domain.com/api
```

### 3. Rate Limiting
Add to `app/Http/Kernel.php` if needed:
```php
'api' => [
    'throttle:60,1',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 4. Token Expiration
To add token expiration, update `config/sanctum.php`:
```php
'expiration' => 60, // 60 minutes
```

---

## 🔧 Customization

### Change Attendance Logic
Edit `AttendanceApiController.php` to modify:
- Period count (currently 8, line 67)
- Teacher detection logic (line 39)
- Attendance status options

### Change Leave Types
Edit `ApplyLeaveRequest.php` to modify allowed leave types:
```php
'leave_type' => 'required|string|in:sick_leave,casual_leave,annual_leave,...'
```

### Modify Response Format
Edit `SalaryResource.php`, `LeaveResource.php`, etc. to customize JSON responses.

---

## 🚀 Deployment Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Update `.env` with correct domain
- [ ] Clear cache: `php artisan config:cache`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure CORS for your app domain
- [ ] Test all endpoints
- [ ] Enable HTTPS
- [ ] Set up rate limiting
- [ ] Monitor logs in `storage/logs/`

---

## 📱 Android App Integration

### Retrofit Example
```kotlin
interface PayrollApiService {
    @POST("login")
    suspend fun login(@Body request: LoginRequest): LoginResponse

    @GET("profile")
    suspend fun getProfile(
        @Header("Authorization") token: String
    ): ProfileResponse

    @GET("attendance/monthly")
    suspend fun getMonthlyAttendance(
        @Header("Authorization") token: String,
        @Query("month") month: Int,
        @Query("year") year: Int
    ): AttendanceResponse
}
```

### HTTP Client Setup
```kotlin
val client = OkHttpClient.Builder()
    .addInterceptor { chain ->
        val original = chain.request()
        val request = original.newBuilder()
            .header("Authorization", "Bearer $token")
            .header("Accept", "application/json")
            .build()
        chain.proceed(request)
    }
    .build()

val retrofit = Retrofit.Builder()
    .baseUrl("https://your-domain.com/api/")
    .client(client)
    .addConverterFactory(GsonConverterFactory.create())
    .build()
```

---

## 🐛 Troubleshooting

### "Unauthenticated" Error
- Verify token is included in Authorization header
- Check if token has expired
- Ensure user is properly linked to employee

### "Employee profile not found"
- Make sure employee has `user_id` set
- Verify migration was run

### CORS Errors
- Check `config/cors.php` configuration
- Verify `APP_URL` in `.env` matches allowed origin
- Add `Content-Type: application/json` header

### Token Mismatch
- Clear browser/app cache
- Generate new token via login
- Check token format: `{number}|{token_string}`

---

## 📞 Support

For issues or questions:
1. Check the full API documentation: `API_DOCUMENTATION.md`
2. Review error messages in `storage/logs/laravel.log`
3. Test endpoints with Postman first
4. Verify database migrations ran successfully

---

**Last Updated:** February 21, 2024
**Version:** 1.0
