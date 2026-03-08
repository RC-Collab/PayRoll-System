<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\TaxSettingController;
use App\Http\Controllers\HolidayController;

/*
|--------------------------------------------------------------------------
| ACCOUNT ACTIVATION ROUTES
|--------------------------------------------------------------------------
| Routes for new employees to activate their accounts
| These are public routes (no authentication required)
*/
Route::prefix('activate')->name('activate.')->group(function () {
    Route::get('/', [ActivationController::class, 'showForm'])->name('form');
    Route::post('/send-code', [ActivationController::class, 'sendCode'])->name('send');
    Route::get('/verify', [ActivationController::class, 'showVerifyForm'])->name('verify.form');
    Route::post('/verify', [ActivationController::class, 'verifyCode'])->name('verify');
    Route::post('/resend', [ActivationController::class, 'resendCode'])->name('resend');
});

/*
|--------------------------------------------------------------------------
| PUBLIC AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
| Login, logout, and password reset routes
| These are public routes (no authentication required)
*/
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTHENTICATED USERS ONLY)
|--------------------------------------------------------------------------
| All routes below this line require the user to be logged in
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ROOT REDIRECT
    |--------------------------------------------------------------------------
    */
    Route::get('/', fn () => redirect()->route('dashboard'));

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATION ROUTES
    |--------------------------------------------------------------------------
    | Routes for managing user notifications
    | All authenticated users can access their own notifications
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // View notifications
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unreadCount');
        
        // Manage notifications
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::post('/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('markAsUnread');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'deleteAll'])->name('deleteAll');
        
        // Create notifications (Admin/HR only)
        Route::middleware('role:admin,hr')->group(function () {
            Route::get('/create', [NotificationController::class, 'createForm'])->name('create');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::post('/batch', [NotificationController::class, 'storeBatch'])->name('storeBatch');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ROUTES
    |--------------------------------------------------------------------------
    | Main dashboard - accessible by admin, hr, accountant
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:admin,hr,accountant')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE ROUTES (VIEW ONLY)
    |--------------------------------------------------------------------------
    | Routes for viewing employee information
    | Accessible by admin, hr, accountant
    */
    Route::middleware('role:admin,hr,accountant')->prefix('employees')->name('employees.')->group(function () {
        // List and view employees
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        // ensure the wildcard only matches numeric ids; otherwise strings like
        // "create" or "edit" would be treated as an {employee} and return a 404
        // due to model binding. The CRUD routes are defined in a later group.
        Route::get('/{employee}', [EmployeeController::class, 'show'])
            ->whereNumber('employee')
            ->name('show');

        // Employee-specific action routes
        Route::get('/{employee}/salary', [SalaryController::class, 'calculateEmployee'])->name('salary.calculate');
        Route::get('/{employee}/attendance/history', [AttendanceController::class, 'employeeHistory'])->name('attendance.history');
        Route::get('/{employee}/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    });

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE MANAGEMENT ROUTES (CRUD)
    |--------------------------------------------------------------------------
    | Routes for creating, updating, and deleting employees
    | Accessible by admin and hr only
    */
    Route::middleware('role:admin,hr')->prefix('employees')->name('employees.')->group(function () {
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        
        // Soft delete management
        Route::get('/{employee}/restore', [EmployeeController::class, 'restore'])
            ->name('restore')
            ->withTrashed();
            
        Route::delete('/{employee}/force-delete', [EmployeeController::class, 'forceDelete'])
            ->name('force-delete')
            ->withTrashed();
    });

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE ROUTES
    |--------------------------------------------------------------------------
    | Routes for managing employee attendance
    | Accessible by admin, hr, accountant
    */
    Route::middleware('role:admin,hr,accountant')->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/history/{employee}', [AttendanceController::class, 'employeeHistory'])->name('history');
        Route::get('/report/{employee}', [AttendanceController::class, 'employeeReport'])->name('employeeReport');
        // allow deletion of a mis‑entered attendance row
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])
            ->middleware('role:admin,hr')
            ->name('destroy');

        // Holiday management
        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays');
        Route::get('/holidays/{id}', [HolidayController::class, 'show']);
        
        // Holiday management (admin/hr only)
        Route::middleware('role:admin,hr')->group(function () {
            Route::post('/holidays/store', [HolidayController::class, 'store'])->name('holidays.store');
            Route::post('/holidays/update', [HolidayController::class, 'update'])->name('holidays.update');
            Route::post('/holidays/bulk-upload', [HolidayController::class, 'bulkUpload'])->name('holidays.bulkUpload');
            Route::delete('/holidays/{id}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | SALARY ROUTES - MAIN SECTION
    |--------------------------------------------------------------------------
    | All salary-related routes grouped under /salary
    */
    Route::prefix('salary')->name('salary.')->group(function () {
        
        /*
        |----------------------------------------------------------------------
        | PUBLIC SALARY ROUTES
        |----------------------------------------------------------------------
        | Routes accessible to all authenticated users
        */
        Route::get('/', [SalaryController::class, 'index'])->name('index');
        Route::get('/history', [SalaryController::class, 'history'])->name('history');
        Route::get('/payslip/{salary}', [SalaryController::class, 'payslip'])->name('payslip');
        Route::get('/calculate-single', [SalaryController::class, 'calculateSingle'])->name('calculate.single');
        
        /*
        |----------------------------------------------------------------------
        | FINE & BONUS ROUTES
        |----------------------------------------------------------------------
        | Routes for applying fines and bonuses to employees
        */
        Route::post('/apply-fine', [SalaryController::class, 'applyFine'])->name('apply.fine');
        Route::post('/apply-bonus', [SalaryController::class, 'applyBonus'])->name('apply.bonus');
        
        /*
        |----------------------------------------------------------------------
        | ADMIN/ACCOUNTANT SALARY ROUTES
        |----------------------------------------------------------------------
        | Routes for salary calculation and management
        | Accessible by admin and accountant only
        */
        Route::middleware('role:admin,accountant')->group(function () {
            Route::post('/calculate', [SalaryController::class, 'calculate'])->name('calculate');
            Route::get('/edit/{salary}', [SalaryController::class, 'edit'])->name('edit');
            Route::put('/update/{salary}', [SalaryController::class, 'update'])->name('update');
            Route::post('/{salary}/mark-paid', [SalaryController::class, 'markAsPaid'])->name('markPaid');

            // payout form/page with cheque/bank details
            Route::get('/{salary}/payout', [SalaryController::class, 'payoutForm'])->name('payout.form');
            Route::post('/{salary}/payout', [SalaryController::class, 'processPayout'])->name('payout.process');
        });

        /*
        |----------------------------------------------------------------------
        | SALARY COMPONENTS ROUTES
        |----------------------------------------------------------------------
        | Routes for managing salary components (allowances/deductions formulas)
        | Accessible by admin and hr only
        */
        Route::middleware('role:admin,hr')->prefix('components')->name('components.')->group(function () {
            Route::get('/', [SalaryComponentController::class, 'index'])->name('index');
            Route::get('/create', [SalaryComponentController::class, 'create'])->name('create');
            Route::post('/', [SalaryComponentController::class, 'store'])->name('store');
            Route::get('/{component}/edit', [SalaryComponentController::class, 'edit'])->name('edit');
            Route::put('/{component}', [SalaryComponentController::class, 'update'])->name('update');
            Route::delete('/{component}', [SalaryComponentController::class, 'destroy'])->name('destroy');
            Route::post('/{component}/toggle-status', [SalaryComponentController::class, 'toggleStatus'])->name('toggleStatus');
        });
        
        /*
        |----------------------------------------------------------------------
        | ALLOWANCES MANAGEMENT ROUTES
        |----------------------------------------------------------------------
        | Routes for managing employee allowances
        | Accessible by admin and hr only
        */
        Route::middleware('role:admin,hr')->prefix('allowances')->name('allowances.')->group(function () {
            Route::get('/', [AllowanceController::class, 'index'])->name('index');
            Route::get('/create', [AllowanceController::class, 'create'])->name('create');
            Route::post('/', [AllowanceController::class, 'store'])->name('store');
            Route::get('/{allowance}/edit', [AllowanceController::class, 'edit'])->name('edit');
            Route::put('/{allowance}', [AllowanceController::class, 'update'])->name('update');
            Route::delete('/{allowance}', [AllowanceController::class, 'destroy'])->name('destroy');
        });

        /*
        |----------------------------------------------------------------------
        | DEDUCTIONS MANAGEMENT ROUTES
        |----------------------------------------------------------------------
        | Routes for managing employee deductions
        | Accessible by admin and hr only
        */
        Route::middleware('role:admin,hr')->prefix('deductions')->name('deductions.')->group(function () {
            Route::get('/', [DeductionController::class, 'index'])->name('index');
            Route::get('/create', [DeductionController::class, 'create'])->name('create');
            Route::post('/', [DeductionController::class, 'store'])->name('store');
            Route::get('/{deduction}/edit', [DeductionController::class, 'edit'])->name('edit');
            Route::put('/{deduction}', [DeductionController::class, 'update'])->name('update');
            Route::delete('/{deduction}', [DeductionController::class, 'destroy'])->name('destroy');
        });

        /*
        |----------------------------------------------------------------------
        | TAX SETTINGS ROUTES
        |----------------------------------------------------------------------
        | Routes for configuring tax slabs and rates
        | Accessible by admin only
        */
        Route::middleware('role:admin')->prefix('tax-settings')->name('tax-settings.')->group(function () {
            Route::get('/', [TaxSettingController::class, 'index'])->name('index');
            Route::post('/update', [TaxSettingController::class, 'update'])->name('update');
        });

        /*
        |----------------------------------------------------------------------
        | SALARY FORMULAS ROUTES (PLACEHOLDER)
        |----------------------------------------------------------------------
        | Placeholder routes for future formula management
        */
        Route::middleware('role:admin,hr')->prefix('formulas')->name('formulas.')->group(function () {
            Route::get('/', function () {
                return redirect()->route('salary.index')->with('info', 'Formulas feature coming soon!');
            })->name('index');
        });

        /*
        |----------------------------------------------------------------------
        | SALARY SETTINGS ROUTES (PLACEHOLDER)
        |----------------------------------------------------------------------
        | Placeholder routes for future salary settings
        */
        Route::middleware('role:admin,hr')->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', function () {
                return redirect()->route('salary.index')->with('info', 'Settings feature coming soon!');
            })->name('index');
            
            Route::post('/store', function (\Illuminate\Http\Request $request) {
                return redirect()->back()->with('error', 'Settings feature not implemented yet.');
            })->name('store');
        });
        
        /*
        |----------------------------------------------------------------------
        | SALARY SLIPS ROUTES
        |----------------------------------------------------------------------
        | Routes for viewing salary slips history
        */
        Route::prefix('slips')->name('slips.')->group(function () {
            Route::get('/history/{employee?}', function ($employee = null) {
                return redirect()->route('salary.history', ['employee' => $employee]);
            })->name('history');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | LEAVE MANAGEMENT ROUTES
    |--------------------------------------------------------------------------
    | Routes for managing employee leave requests
    | Accessible by admin and hr only
    */
    Route::middleware('role:admin,hr')->prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/apply', [LeaveController::class, 'create'])->name('apply');
        Route::post('/apply', [LeaveController::class, 'store'])->name('store');
        Route::get('/check-balance', [LeaveController::class, 'checkBalance'])->name('check-balance');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
        Route::get('/{leave}', [LeaveController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | DEPARTMENT ROUTES
    |--------------------------------------------------------------------------
    | Routes for managing departments
    | Accessible by admin only
    */
    Route::middleware('role:admin')->prefix('departments')->name('departments.')->group(function () {
        // Basic CRUD
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        
        // Department specific routes
        Route::prefix('{department}')->group(function () {
            Route::get('/', [DepartmentController::class, 'show'])->name('show');
            Route::get('/edit', [DepartmentController::class, 'edit'])->name('edit');
            Route::put('/', [DepartmentController::class, 'update'])->name('update');
            Route::delete('/', [DepartmentController::class, 'destroy'])->name('destroy');
            
            // Staff management within department
            Route::get('/manage', [DepartmentController::class, 'manage'])->name('manage');
            Route::post('/add-employee', [DepartmentController::class, 'addEmployee'])->name('addEmployee');
            Route::delete('/remove-employee/{employee}', [DepartmentController::class, 'removeEmployee'])->name('removeEmployee');
            Route::put('/update-role/{employee}', [DepartmentController::class, 'updateEmployeeRole'])->name('updateRole');
            
            // Role management
            Route::post('/add-role', [DepartmentController::class, 'addRole'])->name('addRole');
            Route::post('/remove-role', [DepartmentController::class, 'removeRole'])->name('removeRole');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | SYSTEM SETTINGS ROUTES
    |--------------------------------------------------------------------------
    | Routes for application settings
    | Accessible by admin only
    */
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    /*
    |--------------------------------------------------------------------------
    | API ROUTES (Internal)
    |--------------------------------------------------------------------------
    | Internal API endpoints for AJAX requests
    */
    Route::get('/api/employee-count', function () {
        $count = App\Models\Employee::count();
        return response()->json(['count' => $count]);
    });

    /*
    |--------------------------------------------------------------------------
    | ADDITIONAL ATTENDANCE ROUTES
    |--------------------------------------------------------------------------
    */

});