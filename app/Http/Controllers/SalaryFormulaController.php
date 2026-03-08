<?php

namespace App\Http\Controllers;

use App\Models\SalaryFormula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryFormulaController extends Controller
{
    public function index()
    {
        $formulas = SalaryFormula::with('createdBy')->get();
        $variables = SalaryFormula::getAvailableVariables();
        
        return view('salary.formulas.index', compact('formulas', 'variables'));
    }
    
    public function create()
    {
        $variables = SalaryFormula::getAvailableVariables();
        
        return view('salary.formulas.create', compact('variables'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:salary_formulas',
            'variable_name' => 'required|string|max:100|unique:salary_formulas',
            'formula' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Extract variables from formula
        preg_match_all('/\{([^}]+)\}/', $validated['formula'], $matches);
        $variables = array_unique($matches[1] ?? []);
        
        $validated['variables'] = $variables;
        $validated['created_by'] = Auth::id();
        
        SalaryFormula::create($validated);
        
        return redirect()->route('salary.formulas.index')
            ->with('success', 'Salary formula created successfully!');
    }
    
    public function edit($id)
    {
        $formula = SalaryFormula::findOrFail($id);
        $variables = SalaryFormula::getAvailableVariables();
        
        return view('salary.formulas.edit', compact('formula', 'variables'));
    }
    
    public function update(Request $request, $id)
    {
        $formula = SalaryFormula::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:salary_formulas,name,' . $id,
            'variable_name' => 'required|string|max:100|unique:salary_formulas,variable_name,' . $id,
            'formula' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Extract variables from formula
        preg_match_all('/\{([^}]+)\}/', $validated['formula'], $matches);
        $variables = array_unique($matches[1] ?? []);
        
        $validated['variables'] = $variables;
        
        $formula->update($validated);
        
        return redirect()->route('salary.formulas.index')
            ->with('success', 'Salary formula updated successfully!');
    }
    
    public function destroy($id)
    {
        $formula = SalaryFormula::findOrFail($id);
        $formula->delete();
        
        return redirect()->route('salary.formulas.index')
            ->with('success', 'Salary formula deleted successfully!');
    }
    
    public function testFormula(Request $request)
    {
        $request->validate([
            'formula' => 'required|string',
            'variables' => 'required|array',
        ]);
        
        try {
            $formula = $request->formula;
            $variables = $request->variables;
            
            // Replace variables
            foreach ($variables as $key => $value) {
                $formula = str_replace('{' . $key . '}', $value, $formula);
            }
            
            // Safe evaluation
            $formula = preg_replace('/[^0-9+\-*\/().,\s]/', '', $formula);
            
            if (preg_match('/^[0-9+\-*\/().,\s]+$/', $formula)) {
                $result = eval("return $formula;");
                
                return response()->json([
                    'success' => true,
                    'result' => $result,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid formula: ' . $e->getMessage(),
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Invalid formula',
        ]);
    }
}