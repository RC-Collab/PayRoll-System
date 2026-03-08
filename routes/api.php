<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\SalaryApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\EmployeeProfileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SettingsController;  
use App\Http\Controllers\Auth\ActivationController;

// =====================================================================
// ANDROID APP APIs - Mobile Application Endpoints
// =====================================================================


// API Activation routes
Route::prefix('activate')->group(function () {
    Route::post('send-code', [ActivationController::class, 'apiSendCode']);
    Route::post('verify', [ActivationController::class, 'apiVerifyCode']);
    Route::post('resend', [ActivationController::class, 'apiResendCode']);
});

// Add these after activation routes
Route::post('forgot-password', [ActivationController::class, 'apiSendResetCode']);
Route::post('reset-password', [ActivationController::class, 'apiResetPassword']);

// -----------------------
// PUBLIC AUTHENTICATION ROUTES
// -----------------------
Route::post('/login', [AuthController::class, 'login']);

// -----------------------
// PROTECTED ROUTES (auth:sanctum - Android App Bearer Token)
// -----------------------
Route::middleware('auth:sanctum')->group(function () {

    // =================== AUTHENTICATION APIs ===================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);

    // =================== ATTENDANCE APIs ===================
    Route::prefix('attendance')->group(function () {
        Route::post('/check-in', [AttendanceApiController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceApiController::class, 'checkOut']);
        Route::post('/mark', [AttendanceApiController::class, 'mark'])->middleware('role:admin,hr');
        Route::post('/quick-mark', [AttendanceApiController::class, 'quickMark'])->middleware('role:admin,hr');
        Route::post('/present', [AttendanceApiController::class, 'present']);
        Route::post('/absent', [AttendanceApiController::class, 'absent']);
        Route::get('/today', [AttendanceApiController::class, 'today']);
        Route::get('/history', [AttendanceApiController::class, 'history']);
        Route::get('/summary', [AttendanceApiController::class, 'summary']);
    });

    // =================== LEAVE APIs ===================
    Route::prefix('leave')->group(function () {
        Route::post('/apply', [LeaveApiController::class, 'apply']);
        Route::get('/list', [LeaveApiController::class, 'list']);
        Route::post('/cancel', [LeaveApiController::class, 'cancel']);
    });

    // =================== SALARY APIs ===================
    Route::prefix('salary')->group(function () {
        Route::get('/monthly', [SalaryApiController::class, 'monthly']);
        Route::get('/status', [SalaryApiController::class, 'status']);
        Route::get('/history', [SalaryApiController::class, 'history']);
        Route::get('/receipt/{id}', [SalaryApiController::class, 'receipt']);
    });

    // =================== REPORT APIs ===================
    Route::prefix('report')->group(function () {
        Route::get('/monthly', [ReportApiController::class, 'monthly']);
        Route::get('/yearly', [ReportApiController::class, 'yearly']);
    });

    // =================== EMPLOYEE PROFILE (Self Management) ===================
    Route::prefix('employee/profile')->group(function () {
        // Get complete profile
        Route::get('/', [EmployeeProfileController::class, 'getProfile']);
        
        // Update personal info
        Route::put('/personal', [EmployeeProfileController::class, 'updatePersonal']);
        Route::put('/address', [EmployeeProfileController::class, 'updateAddress']);
        Route::post('/profile-image', [EmployeeProfileController::class, 'updateProfileImage']);
        
        // Qualifications
        Route::prefix('qualifications')->group(function () {
            Route::get('/', [EmployeeProfileController::class, 'getQualifications']);
            Route::post('/', [EmployeeProfileController::class, 'upsertQualification']);
            Route::delete('/{id}', [EmployeeProfileController::class, 'deleteQualification']);
        });
        
        // Work Experience
        Route::prefix('experiences')->group(function () {
            Route::get('/', [EmployeeProfileController::class, 'getExperiences']);
            Route::post('/', [EmployeeProfileController::class, 'upsertExperience']);
            Route::delete('/{id}', [EmployeeProfileController::class, 'deleteExperience']);
        });
        
        // Emergency Contacts
        Route::prefix('emergency-contacts')->group(function () {
            Route::get('/', [EmployeeProfileController::class, 'getEmergencyContacts']);
            Route::post('/', [EmployeeProfileController::class, 'upsertEmergencyContact']);
            Route::delete('/{id}', [EmployeeProfileController::class, 'deleteEmergencyContact']);
        });
    });

    // =================== NOTIFICATION APIs ===================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    }); // 👈 FIXED: Added missing closing brace

    // =================== SETTINGS APIs ===================
    Route::prefix('settings')->group(function () {
        Route::get('/holidays', [SettingsController::class, 'getHolidays']);
        Route::post('/holidays', [SettingsController::class, 'addHoliday'])->middleware('role:admin,hr');
        Route::delete('/holidays/{id}', [SettingsController::class, 'deleteHoliday'])->middleware('role:admin,hr');
        Route::get('/working-days', [SettingsController::class, 'getWorkingDays']);
        Route::post('/working-days', [SettingsController::class, 'saveWorkingDays'])->middleware('role:admin,hr');
    });
}); // 👈 This closes the main auth:sanctum group