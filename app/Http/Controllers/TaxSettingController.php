<?php

namespace App\Http\Controllers;

use App\Models\TaxSetting;
use Illuminate\Http\Request;

class TaxSettingController extends Controller
{
    public function index()
    {
        $taxSetting = TaxSetting::where('is_active', true)->first() ?? new TaxSetting();
        return view('salary.tax-settings', compact('taxSetting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|string',
            'unmarried_slabs' => 'required|array',
            'married_slabs' => 'required|array',
            'pf_percentage' => 'required|numeric|min:0|max:100',
            'ssf_employee_percentage' => 'required|numeric|min:0|max:100',
            'ssf_employer_percentage' => 'required|numeric|min:0|max:100',
            'cit_max_amount' => 'required|numeric|min:0',
            'insurance_max_amount' => 'required|numeric|min:0',
            'medical_max_amount' => 'required|numeric|min:0',
            'remote_area_max_amount' => 'required|numeric|min:0',
        ]);

        TaxSetting::updateOrCreate(
            ['is_active' => true],
            $validated
        );

        return redirect()->back()->with('success', 'Tax settings updated successfully');
    }
}