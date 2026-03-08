<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function index()
    {
        $allowances = Allowance::orderBy('sort_order')->get();
        return view('salary.allowances.index', compact('allowances'));
    }

    public function create()
    {
        return view('salary.allowances.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:allowances',
            'type' => 'required|in:fixed,percentage,formula',
            'default_value' => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'formula' => 'nullable|string',
            'is_taxable' => 'boolean',
            'is_mandatory' => 'boolean',
            'sort_order' => 'integer',
        ]);

        Allowance::create($validated);

        return redirect()->route('salary.allowances.index')
            ->with('success', 'Allowance created successfully');
    }

    public function edit(Allowance $allowance)
    {
        return view('salary.allowances.edit', compact('allowance'));
    }

    public function update(Request $request, Allowance $allowance)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:allowances,code,' . $allowance->id,
            'type' => 'required|in:fixed,percentage,formula',
            'default_value' => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'formula' => 'nullable|string',
            'is_taxable' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $allowance->update($validated);

        return redirect()->route('salary.allowances.index')
            ->with('success', 'Allowance updated successfully');
    }

    public function destroy(Allowance $allowance)
    {
        $allowance->delete();
        return redirect()->route('salary.allowances.index')
            ->with('success', 'Allowance deleted successfully');
    }
}