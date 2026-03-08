<?php

namespace App\Http\Controllers;

use App\Models\SalarySlip;
use App\Models\MonthlySalary;
use Illuminate\Http\Request;
use PDF;

class SalarySlipController extends Controller
{
    public function generate($salaryId)
    {
        $salary = MonthlySalary::with(['employee', 'employee.departments'])->findOrFail($salaryId);
        
        // Create salary slip
        $slip = SalarySlip::create([
            'salary_id' => $salary->id,
            'slip_number' => SalarySlip::generateSlipNumber(),
            'components_breakdown' => $this->getComponentsBreakdown($salary),
            'attendance_summary' => $salary->attendance_summary ?? [],
            'tax_calculation' => $this->getTaxCalculation($salary),
            'issued_by' => auth()->user()->name,
            'issue_date' => now(),
            'payment_method' => 'Bank Transfer',
            'bank_name' => $salary->employee->bank_name,
            'account_number' => $salary->employee->account_number,
        ]);
        
        return redirect()->route('salary.slips.show', $slip->id)
            ->with('success', 'Salary slip generated successfully!');
    }
    
    public function show($id)
    {
        $slip = SalarySlip::with('salary.employee')->findOrFail($id);
        
        return view('salary.slips.show', compact('slip'));
    }
    
    public function downloadPdf($id)
    {
        $slip = SalarySlip::with('salary.employee')->findOrFail($id);
        
        $pdf = PDF::loadView('salary.slips.pdf', compact('slip'));
        
        return $pdf->download("salary-slip-{$slip->slip_number}.pdf");
    }
    
    public function history($employeeId = null)
    {
        $query = SalarySlip::with('salary.employee');
        
        if ($employeeId) {
            $query->whereHas('salary', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            });
        }
        
        $slips = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('salary.slips.history', compact('slips'));
    }
    
    private function getComponentsBreakdown($salary)
    {
        // This would come from your salary calculation
        return [
            [
                'name' => 'Basic Salary',
                'type' => 'allowance',
                'amount' => $salary->basic_salary,
            ],
            [
                'name' => 'Dearness Allowance',
                'type' => 'allowance',
                'amount' => $salary->dearness_allowance,
            ],
            [
                'name' => 'Provident Fund',
                'type' => 'deduction',
                'amount' => $salary->provident_fund,
            ],
            // Add other components...
        ];
    }
    
    private function getTaxCalculation($salary)
    {
        $gross = $salary->gross_salary ?: 0;
        $annual = $gross * 12;
        $taxPct = 0;
        if ($gross > 0 && $salary->income_tax > 0) {
            $taxPct = ($salary->income_tax / $gross) * 100;
        }

        return [
            'annual_income' => $annual,
            'tax_slab' => $this->getTaxSlab($annual),
            'tax_amount' => $salary->income_tax,
            'tax_percentage' => $taxPct,
        ];
    }
    
    private function getTaxSlab($annualIncome)
    {
        if ($annualIncome <= 500000) return '1%';
        if ($annualIncome <= 700000) return '10%';
        if ($annualIncome <= 1000000) return '20%';
        if ($annualIncome <= 2000000) return '30%';
        return '36%';
    }
}