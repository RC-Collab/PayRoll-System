<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryComponentController extends Controller
{
    public function index()
    {
        $components = SalaryComponent::with('createdBy')->orderBy('type')->orderBy('sort_order')->get();
        $employees = Employee::active()->get();
        $departments = Department::all();
        
        return view('salary.components.index', compact('components', 'employees', 'departments'));
    }
    
    public function create()
    {
        $employees = Employee::active()->get();
        $departments = Department::all();
        
        return view('salary.components.create', compact('employees', 'departments'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction,bonus,other',
            'calculation_type' => 'required|in:fixed,percentage,formula,attendance_based',
            'fixed_amount' => 'nullable|required_if:calculation_type,fixed|numeric|min:0',
            'percentage' => 'nullable|required_if:calculation_type,percentage|numeric|min:0|max:100',
            'formula' => 'nullable|required_if:calculation_type,formula|string',
            'attendance_field' => 'nullable|required_if:calculation_type,attendance_based|string',
            'attendance_rate' => 'nullable|required_if:calculation_type,attendance_based|numeric|min:0',
            'is_active' => 'boolean',
            'applicable_to' => 'required|in:all,department,employee,designation',
            'applicable_ids' => 'nullable|array',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
        ]);
        
        $validated['created_by'] = Auth::id();
        
        // Convert applicable_ids to JSON if provided
        if ($request->has('applicable_ids')) {
            $validated['applicable_ids'] = json_encode($request->applicable_ids);
        } else {
            $validated['applicable_ids'] = null;
        }
        
        SalaryComponent::create($validated);
        
        return redirect()->route('salary.components.index')
            ->with('success', 'Salary component created successfully!');
    }
    
    public function edit($id)
    {
        $component = SalaryComponent::findOrFail($id);
        $employees = Employee::active()->get();
        $departments = Department::all();
        
        return view('salary.components.edit', compact('component', 'employees', 'departments'));
    }
    
    public function update(Request $request, $id)
    {
        $component = SalaryComponent::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction,bonus,other',
            'calculation_type' => 'required|in:fixed,percentage,formula,attendance_based',
            'fixed_amount' => 'nullable|required_if:calculation_type,fixed|numeric|min:0',
            'percentage' => 'nullable|required_if:calculation_type,percentage|numeric|min:0|max:100',
            'formula' => 'nullable|required_if:calculation_type,formula|string',
            'attendance_field' => 'nullable|required_if:calculation_type,attendance_based|string',
            'attendance_rate' => 'nullable|required_if:calculation_type,attendance_based|numeric|min:0',
            'is_active' => 'boolean',
            'applicable_to' => 'required|in:all,department,employee,designation',
            'applicable_ids' => 'nullable|array',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
        ]);
        
        // Convert applicable_ids to JSON if provided
        if ($request->has('applicable_ids')) {
            $validated['applicable_ids'] = json_encode($request->applicable_ids);
        } else {
            $validated['applicable_ids'] = null;
        }
        
        $component->update($validated);
        
        return redirect()->route('salary.components.index')
            ->with('success', 'Salary component updated successfully!');
    }
    
    public function destroy($id)
    {
        $component = SalaryComponent::findOrFail($id);
        $component->delete();
        
        return redirect()->route('salary.components.index')
            ->with('success', 'Salary component deleted successfully!');
    }
    
    public function toggleStatus($id)
    {
        $component = SalaryComponent::findOrFail($id);
        $component->is_active = !$component->is_active;
        $component->save();
        
        return response()->json([
            'success' => true,
            'is_active' => $component->is_active
        ]);
    }
}