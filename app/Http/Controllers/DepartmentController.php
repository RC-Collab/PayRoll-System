<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * Display a listing of all departments
     */
    public function index()
    {
        // Get all departments with employee count
        $departments = Department::withCount('employees')
            ->orderBy('name')
            ->get();
        
        // Calculate statistics
        $totalDepartments = $departments->count();
        $totalEmployees = $departments->sum('employees_count');
        $totalRoles = $departments->sum(function($dept) {
            return is_array($dept->roles) ? count($dept->roles) : 0;
        });
        
        // Count departments with HOD
        $hodPositions = $departments->filter(function($dept) {
            return !empty($dept->head_of_department);
        })->count();
        
        return view('departments.index', compact(
            'departments', 
            'totalDepartments', 
            'totalEmployees', 
            'totalRoles',
            'hodPositions'
        ));
    }
    
    /**
     * Show create department form
     */
    public function create()
    {
        // Get all active employees for HOD selection
        $employees = Employee::where('is_active', true)
            ->orderBy('first_name')
            ->get();
        
        return view('departments.create', compact('employees'));
    }
    
    /**
     * Store new department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments',
            'description' => 'nullable|string',
            'category' => 'required|in:academic,administrative,support,technical,operations',
            'head_of_department_id' => 'nullable|exists:employees,id',
            'icon' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // Process roles if provided
        $roles = [];
        if ($request->has('roles') && is_array($request->roles)) {
            $roles = array_filter(array_map('trim', $request->roles));
        }
        
        // If HOD is selected, get employee name
        if ($request->head_of_department_id) {
            $hodEmployee = Employee::find($request->head_of_department_id);
            $validated['head_of_department'] = $hodEmployee->full_name;
        } else {
            $validated['head_of_department'] = null;
        }
        
        // Remove the ID field as we don't store it in department table
        unset($validated['head_of_department_id']);
        
        // Add roles to validated data
        $validated['roles'] = $roles;
        
        // Create department
        $department = Department::create($validated);
        
        // If HOD is selected, automatically add them to the department with HOD role
        if ($request->head_of_department_id) {
            $department->employees()->attach($request->head_of_department_id, [
                'role' => 'Head of Department',
                'is_primary' => true,
                'start_date' => now()
            ]);
        }
        
        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully!');
    }
    
    /**
     * Display department details
     */
    public function show($id)
    {
        $department = Department::with(['employees' => function($query) {
            $query->where('is_active', true)
                  ->withPivot('role');
        }])->findOrFail($id);
        
        // Get employees for this department
        $employees = $department->employees ?? collect();
        
        return view('departments.view', compact('department', 'employees'));
    }
    
    /**
     * Show department edit form
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);
        
        // Get all active employees for HOD selection
        $employees = Employee::where('is_active', true)
            ->orderBy('first_name')
            ->get();
        
        // Find current HOD employee if exists
        $currentHod = null;
        if ($department->head_of_department) {
            // Try to find by name or from department employees
            $currentHod = $department->employees()
                ->wherePivot('role', 'Head of Department')
                ->first();
        }
        
        return view('departments.edit', compact('department', 'employees', 'currentHod'));
    }
    
    /**
     * Update department
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'category' => 'required|in:academic,administrative,support,technical,operations',
            'head_of_department_id' => 'nullable|exists:employees,id',
            'icon' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        // Process roles if provided
        $roles = [];
        if ($request->has('roles') && is_array($request->roles)) {
            $roles = array_filter(array_map('trim', $request->roles));
        }
        
        // Handle HOD change
        $oldHod = $department->employees()
            ->wherePivot('role', 'Head of Department')
            ->first();
            
        if ($request->head_of_department_id) {
            $hodEmployee = Employee::find($request->head_of_department_id);
            $validated['head_of_department'] = $hodEmployee->full_name;
            
            // Remove old HOD role if exists
            if ($oldHod) {
                $department->employees()->updateExistingPivot($oldHod->id, [
                    'role' => 'Member'
                ]);
            }
            
            // Add new HOD to department if not already there
            if (!$department->employees()->where('employee_id', $hodEmployee->id)->exists()) {
                $department->employees()->attach($hodEmployee->id, [
                    'role' => 'Head of Department',
                    'is_primary' => true,
                    'start_date' => now()
                ]);
            } else {
                // Update role to HOD
                $department->employees()->updateExistingPivot($hodEmployee->id, [
                    'role' => 'Head of Department'
                ]);
            }
        } else {
            $validated['head_of_department'] = null;
            
            // Remove HOD role from old HOD
            if ($oldHod) {
                $department->employees()->updateExistingPivot($oldHod->id, [
                    'role' => 'Member'
                ]);
            }
        }
        
        // Remove the ID field
        unset($validated['head_of_department_id']);
        
        // Add roles to validated data
        $validated['roles'] = $roles;
        
        // Update department
        $department->update($validated);
        
        return redirect()->route('departments.show', $department->id)
            ->with('success', 'Department updated successfully!');
    }
    
    /**
     * Manage department staff (add/remove employees)
     */
    public function manage($id)
    {
        $department = Department::with(['employees' => function($query) {
            $query->withPivot('role', 'is_primary');
        }])->findOrFail($id);
        
        // Get current staff in this department
        $currentStaff = $department->employees;
        
        // Get available staff NOT in this department
        $availableStaff = Employee::where('is_active', true)
            ->whereDoesntHave('departments', function($query) use ($id) {
                $query->where('department_id', $id);
            })
            ->orderBy('first_name')
            ->get();
        
        // Get department roles for dropdown
        $departmentRoles = is_array($department->roles) ? $department->roles : [];
        
        return view('departments.manage', compact(
            'department', 
            'currentStaff', 
            'availableStaff',
            'departmentRoles'
        ));
    }
    
    /**
     * Add employee to department
     */
    public function addEmployee(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'role' => 'nullable|string|max:255',
            'is_primary' => 'boolean'
        ]);
        
        $department = Department::findOrFail($id);
        $employee = Employee::findOrFail($request->employee_id);
        
        // Check if employee already in department
        if ($department->employees()->where('employee_id', $employee->id)->exists()) {
            return back()->with('error', 'Employee already in this department!');
        }
        
        // Attach employee with role
        $department->employees()->attach($employee->id, [
            'role' => $request->role ?? 'Member',
            'is_primary' => $request->is_primary ?? false,
            'start_date' => now()
        ]);
        
        return back()->with('success', 'Employee added to department successfully!');
    }
    
    /**
     * Remove employee from department
     */
    public function removeEmployee($departmentId, $employeeId)
    {
        $department = Department::findOrFail($departmentId);
        $employee = Employee::findOrFail($employeeId);
        
        // Check if this employee is HOD
        if ($department->head_of_department == $employee->full_name) {
            return back()->with('error', 'Cannot remove Head of Department. Change HOD first.');
        }
        
        $department->employees()->detach($employee->id);
        
        return back()->with('success', 'Employee removed from department successfully!');
    }
    
    /**
     * Update employee role in department
     */
    public function updateEmployeeRole(Request $request, $departmentId, $employeeId)
    {
        $request->validate([
            'role' => 'required|string|max:255'
        ]);
        
        $department = Department::findOrFail($departmentId);
        $employee = Employee::findOrFail($employeeId);
        
        // If setting as HOD, update department HOD field
        if ($request->role === 'Head of Department') {
            $department->update(['head_of_department' => $employee->full_name]);
            
            // Remove HOD role from any other employee
            $department->employees()
                ->where('employee_id', '!=', $employeeId)
                ->wherePivot('role', 'Head of Department')
                ->each(function ($emp) use ($department) {
                    $department->employees()->updateExistingPivot($emp->id, [
                        'role' => 'Member'
                    ]);
                });
        }
        
        $department->employees()->updateExistingPivot($employeeId, [
            'role' => $request->role
        ]);
        
        return back()->with('success', 'Employee role updated successfully!');
    }
    
    /**
     * Add new role to department
     */
    public function addRole(Request $request, $id)
    {
        $request->validate([
            'role_name' => 'required|string|max:255'
        ]);
        
        $department = Department::findOrFail($id);
        
        // Get current roles
        $currentRoles = $department->roles ?? [];
        
        // Add new role if not already exists
        if (!in_array($request->role_name, $currentRoles)) {
            $currentRoles[] = $request->role_name;
            $department->update(['roles' => $currentRoles]);
        }
        
        return back()->with('success', 'Role added successfully!');
    }
    
    /**
     * Remove role from department
     */
    public function removeRole(Request $request, $id)
    {
        $request->validate([
            'role_name' => 'required|string|max:255'
        ]);
        
        $department = Department::findOrFail($id);
        
        // Get current roles
        $currentRoles = $department->roles ?? [];
        
        // Remove role if exists
        $key = array_search($request->role_name, $currentRoles);
        if ($key !== false) {
            unset($currentRoles[$key]);
            $currentRoles = array_values($currentRoles); // Re-index array
            $department->update(['roles' => $currentRoles]);
        }
        
        return back()->with('success', 'Role removed successfully!');
    }
    
    /**
     * Delete department
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete department with employees. Remove employees first.');
        }
        
        $department->delete();
        
        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully!');
    }
}