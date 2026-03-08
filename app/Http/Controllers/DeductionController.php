<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Deduction;

class DeductionController extends Controller
{
    public function index()
    {
        $deductions = Deduction::orderBy('sort_order')->get();
        return view('salary.deductions.index', compact('deductions'));
    }

    public function create()
    {
        return view('salary.deductions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:deductions',
            'type' => 'required|in:fixed,percentage,formula',
            'default_value' => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'max_amount' => 'nullable|numeric|min:0',
            'formula' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
        ]);

        Deduction::create($validated);

        return redirect()->route('salary.deductions.index')
            ->with('success', 'Deduction created successfully');
    }

    public function edit(Deduction $deduction)
    {
        return view('salary.deductions.edit', compact('deduction'));
    }

    public function update(Request $request, Deduction $deduction)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:deductions,code,' . $deduction->id,
            'type' => 'required|in:fixed,percentage,formula',
            'default_value' => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'max_amount' => 'nullable|numeric|min:0',
            'formula' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
        ]);

        $deduction->update($validated);

        return redirect()->route('salary.deductions.index')
            ->with('success', 'Deduction updated successfully');
    }

    public function destroy(Deduction $deduction)
    {
        $deduction->delete();
        return redirect()->route('salary.deductions.index')
            ->with('success', 'Deduction deleted successfully');
    }
}
