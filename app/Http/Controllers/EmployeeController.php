<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use App\Models\PasswordHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('departments')->latest();

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%")
                  ->orWhere('mobile_number', 'like', "%{$request->search}%")
                  ->orWhere('citizenship_number', 'like', "%{$request->search}%");
            });
        }

        // Filter by employment_status
        if ($request->status && $request->status != 'all') {
            $query->where('employment_status', $request->status);
        }

        // Filter by employee_type
        if ($request->employee_type && $request->employee_type != 'all') {
            $query->where('employee_type', $request->employee_type);
        }

        // Filter by department
        if ($request->department && $request->department != 'all') {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department);
            });
        }

        // Statistics
        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('employment_status', 'active')->count(),
            'onLeave' => Employee::where('employment_status', 'on-leave')->count(),
            'newThisMonth' => Employee::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count()
        ];

        $departments = Department::all();
        $employees = $query->paginate(20)->withQueryString();

        return view('employees.index', compact('employees', 'stats', 'departments'));
    }

    public function create()
    {
        // log for debugging: ensure we actually hit this method and know who is logged in
        \Log::info('EmployeeController@create accessed', [
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'user_role' => auth()->user()?->role,
        ]);

        $departments = Department::all();
        $employeeCode = 'EMP' . str_pad((Employee::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        
        return view('employees.create', compact('departments', 'employeeCode'));
    }

    public function store(Request $request)
{
    // Only essential fields are required
    $validated = $request->validate([
        // === BASIC INFORMATION (ONLY THESE ARE REQUIRED) ===
        'employee_code' => 'required|unique:employees|max:20',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'email' => 'required|email|unique:employees|max:100',
        'mobile_number' => 'required|digits:10|unique:employees,mobile_number',
        'gender' => 'required|in:male,female,other',
        
        // === EMPLOYMENT (ONLY THESE ARE REQUIRED) ===
        'joining_date' => 'required|date',
        'employee_type' => 'required|in:permanent,contract,temporary,probation,part-time',
        'designation' => 'required|string|max:100',
        
        // === BANK DETAILS (ONLY THESE ARE REQUIRED) ===
        'bank_name' => 'required|string|max:100',
        'account_number' => 'required|string|max:50',
        'account_holder_name' => 'required|string|max:100',
        'branch_name' => 'required|string|max:100',
        
        // === PASSWORD (REQUIRED ONLY FOR ACTIVE EMPLOYEES) ===
        'password' => 'required_if:employment_status,active|min:8|confirmed',
        'password_confirmation' => 'required_if:employment_status,active',
        
        // === DASHBOARD ROLE (admin only) ===
        'role' => 'nullable|in:admin,hr,accountant,employee,manager',
        
        // === ALL OTHER FIELDS ARE OPTIONAL ===
        'middle_name' => 'nullable|string|max:50',
        'alternate_phone' => 'nullable|digits:10',
        'date_of_birth' => 'nullable|date',
        'marital_status' => 'nullable|in:single,married,divorced,widowed',
        'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        'citizenship_number' => 'nullable|string|max:20',
        'citizenship_issue_date' => 'nullable|date',
        'citizenship_issued_district' => 'nullable|in:'.implode(',', config('nepal.districts')).'',
        'pan_number' => 'nullable|string|max:20',
        'current_address' => 'nullable|string|max:500',
        'permanent_address' => 'nullable|string|max:500',
        'district' => 'nullable|in:'.implode(',', config('nepal.districts')).'',
        'municipality' => 'nullable|string|max:50',
        'ward_number' => 'nullable|string|max:10',
        'emergency_contact_name' => 'nullable|string|max:100',
        'emergency_contact_phone' => 'nullable|digits:10',
        'emergency_contact_relation' => 'nullable|string|max:50',
        'contract_end_date' => 'nullable|date|after:joining_date',
        'qualification' => 'nullable|string|max:100',
        'institution_name' => 'nullable|string|max:100',
        'experience_years' => 'nullable|integer|min:0|max:50',
        'basic_salary' => 'nullable|numeric|min:0',
        'overtime_rate' => 'nullable|numeric|min:0',
        'employment_status' => 'nullable|in:active,inactive,on-leave,suspended,terminated',
        'profile_image' => 'nullable|image|max:2048',
        'is_active' => 'nullable|boolean',
        'notes' => 'nullable|string|max:1000',
        'departments' => 'nullable|array',
        'departments.*' => 'exists:departments,id',
        'department_roles' => 'nullable|array',
        'department_roles.*' => 'nullable|string|max:100'
    ], [
        // Custom Error Messages for required fields only
        'employee_code.required' => 'Employee code is required',
        'first_name.required' => 'First name is required',
        'last_name.required' => 'Last name is required',
        'email.required' => 'Email is required',
        'mobile_number.required' => 'Mobile number is required',
        'gender.required' => 'Gender is required',
        'joining_date.required' => 'Joining date is required',
        'employee_type.required' => 'Employee type is required',
        'designation.required' => 'Designation is required',
        'bank_name.required' => 'Bank name is required',
        'account_number.required' => 'Account number is required',
        'account_holder_name.required' => 'Account holder name is required',
        'branch_name.required' => 'Branch name is required',
    ]);

    DB::beginTransaction();
    try {
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('employees/profile', 'public');
        }

        // Set default values
        $validated['is_active'] = $request->has('is_active') ? true : true; // Default to active
        $validated['employment_status'] = $validated['employment_status'] ?? 'active'; // Default to active
        
        // Create employee (without department and salary data)
        $employeeData = $validated;
        unset($employeeData['departments'], $employeeData['department_roles'], $employeeData['basic_salary'], $employeeData['password'], $employeeData['password_confirmation']);
        
        $employee = Employee::create($employeeData);

// Create user account when activating employee.  Once an account
            // exists we deliberately ignore any further password values, since
            // HR/admins shouldn't be changing passwords after activation.
            if ($validated['employment_status'] === 'active' && empty($employee->user) && !empty($validated['password'])) {
                $user = User::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => strtolower(trim($validated['email'])),
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['role'] ?? 'employee',
                ]);

                // immediately activate/verify accounts created by admins so they
                // can sign in without going through email workflow.
                if ($user) {
                    $user->email_verified_at = now();
                    $user->is_active = true;
                    $user->save();
                }

                $employee->user_id = $user->id;
                $employee->save();

            // Log password creation
            PasswordHistory::logPasswordChange(
                $user->id,
                'created',
                auth()->id(),
                'Password created during employee creation',
                $request
            );
        }

        // Create salary structure with basic salary if provided
        if (!empty($validated['basic_salary']) || isset($validated['overtime_rate'])) {
            $employee->salaryStructure()->create([
                'basic_salary' => $validated['basic_salary'] ?? 0,
                'overtime_rate' => $validated['overtime_rate'] ?? 0,
                'effective_from' => now()->format('Y-m-d'),
                'is_current' => true
            ]);
        }

        // Attach departments if selected
        if ($request->has('departments') && !empty($request->departments)) {
            $departmentsToAttach = [];
            foreach ($request->departments as $departmentId) {
                $role = $request->input("department_roles.{$departmentId}");
                $departmentsToAttach[$departmentId] = [
                    'role' => $role ?? 'Employee', // Default role
                    'joined_date' => now()
                ];
            }
            $employee->departments()->attach($departmentsToAttach);
        }

        DB::commit();

        return redirect()->route('employees.show', $employee->id)
            ->with('success', 'Employee added successfully! You can add more details later.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error: ' . $e->getMessage())
            ->withInput();
    }
}

    public function show(Employee $employee)
    {
        $employee->load([
            'departments',
            'salaryStructure',
            'monthlySalaries' => function ($query) {
                $query->orderBy('salary_month', 'desc')->take(6);
            }
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('departments');
        $departments = Department::all();
        
        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            // === PERSONAL INFORMATION ===
            'employee_code' => "required|unique:employees,employee_code,{$employee->id}|max:20",
            'first_name' => 'required|string|max:50',
'middle_name' => 'nullable|string|max:50', // Changed to nullable
            'last_name' => 'required|string|max:50',
            'email' => "required|email|unique:employees,email,{$employee->id}|max:100",
            'mobile_number' => "required|string|max:15|unique:employees,mobile_number,{$employee->id}",
            
            // Personal Details
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'blood_group' => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            
            // === CITIZENSHIP & IDENTIFICATION ===
            'citizenship_number' => "required|unique:employees,citizenship_number,{$employee->id}|max:20",
            'citizenship_issue_date' => 'required|date',
            'citizenship_issued_district' => 'required|in:'.implode(',', config('nepal.districts')).'',
            'pan_number' => 'required|string|max:20',
            
            // === ADDRESS INFORMATION ===
            'current_address' => 'required|string|max:500',
            'permanent_address' => 'required|string|max:500',
            'district' => 'required|in:'.implode(',', config('nepal.districts')).'',
            'municipality' => 'required|string|max:50',
            'ward_number' => 'required|string|max:10',
            
            // === EMERGENCY CONTACT ===
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|digits:10',
            'emergency_contact_relation' => 'required|string|max:50',
            
            // === EMPLOYMENT DETAILS ===
            'joining_date' => 'required|date',
            'employee_type' => 'required|in:permanent,contract,temporary,probation,part-time',
            'employment_status' => 'required|in:active,inactive,on-leave,suspended,terminated',
'contract_end_date' => 'nullable|date|after:joining_date', // Changed to nullable
            'designation' => 'required|string|max:100',
            'qualification' => 'required|string|max:100',
            'institution_name' => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0|max:50',
            'basic_salary' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            
            // === PASSWORD (optional on update; used only during activation)
            // if the employee already has a linked user account we don't
            // require or even expect a password change from HR.
            'password' => 'nullable|min:8|confirmed',
            'password_confirmation' => 'required_with:password',
            
            // === DASHBOARD ROLE (admin only) ===
            'role' => 'nullable|in:admin,hr,accountant,employee,manager',
            
            // === BANK DETAILS ===
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'branch_name' => 'required|string|max:100',
            
            // === OTHER FIELDS ===
            'profile_image' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
            
            // Departments
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:departments,id',
'department_roles' => 'nullable|array', // Changed to nullable
'department_roles.*' => 'nullable|string|max:100' // Changed to nullable
        ]);

        DB::beginTransaction();
        try {
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($employee->profile_image) {
                    Storage::disk('public')->delete($employee->profile_image);
                }
                $validated['profile_image'] = $request->file('profile_image')->store('employees/profile', 'public');
            }

            // Update employee (without department, salary data, and password)
            $employeeData = $validated;
            unset($employeeData['departments'], $employeeData['department_roles'], $employeeData['basic_salary'], $employeeData['password'], $employeeData['password_confirmation']);
            
            $employee->update($employeeData);

            // Handle user account for active employees.  Only create a new
            // user when one doesn't already exist; do not allow HR to change
            // passwords once the account is established.
            if ($validated['employment_status'] === 'active') {
                $user = $employee->user;

                // create account only if it is missing and a password was provided
                if (!$user && !empty($validated['password'])) {
                    $user = User::create([
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'email' => strtolower(trim($employee->email)),
                        'password' => Hash::make($validated['password']),
                        'role' => $validated['role'] ?? 'employee'
                    ]);
                    $employee->user_id = $user->id;
                    $employee->save();

                    PasswordHistory::logPasswordChange(
                        $user->id,
                        'created',
                        auth()->id(),
                        'Password created during employee update',
                        $request
                    );
                }

                // if there is already a linked user and admin provided a role,
                // update it (admins only see the role field anyway)
                if ($user && isset($validated['role'])) {
                    $user->role = $validated['role'];
                    // upgrading role should also ensure account is active/verified
                    $user->email_verified_at = $user->email_verified_at ?? now();
                    $user->is_active = true;
                    $user->save();
                }
                // ignore $validated['password'] otherwise; if admins need to reset
                // they should use the dedicated password-reset workflow.
            }

            // Update or create salary structure with basic salary (validated may omit these keys)
            if ((isset($validated['basic_salary']) && $validated['basic_salary']) || isset($validated['overtime_rate'])) {
                $employee->salaryStructure()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'basic_salary' => $validated['basic_salary'] ?? 0,
                        'overtime_rate' => $validated['overtime_rate'] ?? 0,
                        'effective_from' => now()->format('Y-m-d'),
                        'is_current' => true
                    ]
                );
            }

            // Sync departments
            $departmentsToSync = [];
            foreach ($request->departments as $departmentId) {
                $departmentsToSync[$departmentId] = [
                    'role' => $request->input("department_roles.{$departmentId}"),
                    'joined_date' => now()
                ];
            }
            $employee->departments()->sync($departmentsToSync);

            DB::commit();

            return redirect()->route('employees.show', $employee->id)
                ->with('success', 'Employee updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        DB::beginTransaction();
        try {
            // Delete profile image if exists
            if ($employee->profile_image) {
                Storage::disk('public')->delete($employee->profile_image);
            }
            
            // Detach all departments
            $employee->departments()->detach();
            
            // Delete employee
            $employee->delete();

            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->restore();

        return redirect()->route('employees.index')
            ->with('success', 'Employee restored successfully!');
    }

    public function forceDelete($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);
        
        // Delete profile image if exists
        if ($employee->profile_image) {
            Storage::disk('public')->delete($employee->profile_image);
        }
        
        $employee->forceDelete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee permanently deleted!');
    }
}