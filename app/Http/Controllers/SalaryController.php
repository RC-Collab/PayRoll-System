<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MonthlySalary;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\LeaveRecord;
use App\Models\SalaryComponent;
use App\Models\SalaryFormula;
use App\Models\SalarySetting;
use App\Models\SalarySlip;
use App\Models\TaxSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        // always store month as YYYY-MM; strip any day/time portion that
        // may have been passed in (e.g. from a date picker)
        $currentMonth = substr($request->month ?: Carbon::now()->format('Y-m'), 0, 7);
        
        // always show all active employees regardless of salary status; the
        // view will control whether calculation/payout actions are enabled.
        $query = Employee::with(['departments', 'monthlySalaries' => function($q) use ($currentMonth) {
            $q->where('salary_month', $currentMonth);
        }])->where('employment_status', 'active');
        
        if ($request->department) {
            $query->whereHas('departments', function($q) use ($request) {
                $q->where('departments.id', $request->department);
            });
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_code', 'like', '%' . $request->search . '%');
            });
        }
        
        $employees = $query->paginate(20)->withQueryString();
        $departments = Department::all();
        $allEmployees = Employee::active()->get();
        
        // Determine whether the selected month has already ended.  Only past
        // months (strictly less than current YYYY-MM) are considered closed.
        $monthEnded = $currentMonth < Carbon::now()->format('Y-m');
        
        // Statistics
        $stats = [
            'totalEmployees' => Employee::active()->count(),
            'currentMonthPayroll' => MonthlySalary::where('salary_month', $currentMonth)
                ->where('payment_status', 'paid')
                ->sum('net_salary'),
            // count of employees who still appear on the index page (no record or still pending)
            'pendingSalaryCount' => Employee::active()
                ->where(function($q) use ($currentMonth) {
                    $q->whereDoesntHave('monthlySalaries', function($q2) use ($currentMonth) {
                        $q2->where('salary_month', $currentMonth);
                    })
                    ->orWhereHas('monthlySalaries', function($q2) use ($currentMonth) {
                        $q2->where('salary_month', $currentMonth)
                           ->whereIn('payment_status', ['pending', 'calculated']);
                    });
                })->count(),
            'paidThisMonth' => MonthlySalary::where('salary_month', $currentMonth)
                ->where('payment_status', 'paid')
                ->count(),
            'totalAbsentDeductions' => 0,
            'totalManualFines' => 0,
        ];

        // Only try to sum these columns if they exist
        if (Schema::hasColumn('monthly_salaries', 'absent_deduction')) {
            $stats['totalAbsentDeductions'] = MonthlySalary::where('salary_month', $currentMonth)
                ->sum('absent_deduction');
        }

        if (Schema::hasColumn('monthly_salaries', 'manual_fine')) {
            $stats['totalManualFines'] = MonthlySalary::where('salary_month', $currentMonth)
                ->sum('manual_fine');
        }
        
        // pass monthEnded flag so the blade can disable actions for open months
        return view('salary.index', compact(
            'employees', 'departments', 'allEmployees',
            'stats', 'currentMonth', 'monthEnded'
        ));
    }
    
    /**
     * Calculate salary for a single employee (per-day deduction logic)
     */
    public function calculateSingle(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
        ]);

        if ($request->month >= Carbon::now()->format('Y-m')) {
            return redirect()->route('salary.index', ['month' => $request->month])
                ->with('error', 'Cannot calculate salary for an open or future month');
        }
        
        $employee = Employee::with('salaryStructure')->findOrFail($request->employee_id);
        
        if (!$employee->salaryStructure) {
            return redirect()->route('salary.index', ['month' => $request->month])
                ->with('error', "Employee {$employee->full_name} does not have a salary structure!");
        }
        
        $salary = $this->calculateEmployeeSalary($employee, $request->month);
        
        return redirect()->route('salary.index', ['month' => $request->month])
            ->with('success', "Salary calculated for {$employee->full_name}!");
    }
    
    /**
     * Bulk salary calculation
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'calculate_for' => 'required|in:all,department,employee',
            'department_id' => 'nullable|exists:departments,id',
            'employee_id' => 'nullable|exists:employees,id',
        ]);
        
        // do not allow calculating if month has not yet completed
        if ($request->month >= Carbon::now()->format('Y-m')) {
            return redirect()->route('salary.index', ['month' => $request->month])
                ->with('error', 'Cannot calculate salary for an open or future month');
        }
        
        $employeesQuery = Employee::with('salaryStructure')->active();
        
        if ($request->calculate_for == 'department' && $request->department_id) {
            $employeesQuery->whereHas('departments', function($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        } elseif ($request->calculate_for == 'employee' && $request->employee_id) {
            $employeesQuery->where('id', $request->employee_id);
        }
        
        $employees = $employeesQuery->get();
        $calculatedCount = 0;
        $employeesWithoutStructure = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($employees as $employee) {
                if ($employee->salaryStructure) {
                    $this->calculateEmployeeSalary($employee, $request->month);
                    $calculatedCount++;
                } else {
                    $employeesWithoutStructure[] = $employee->full_name;
                }
            }
            
            DB::commit();
            
            $message = "Salary calculated for {$calculatedCount} employees!";
            
            if (!empty($employeesWithoutStructure)) {
                $message .= " No salary structure for: " . implode(', ', $employeesWithoutStructure);
            }
            
            return redirect()->route('salary.index', ['month' => $request->month])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('salary.index', ['month' => $request->month])
                ->with('error', 'Error calculating salaries: ' . $e->getMessage());
        }
    }
    
    /**
     * Core salary calculation logic - NEPAL STANDARD
     */
    private function calculateEmployeeSalary($employee, $month)
    {
        // Ensure month is in correct format (YYYY-MM)
        $formattedMonth = substr($month, 0, 7); // Take only YYYY-MM part if longer
        
        // Get salary structure
        $basicSalary = $employee->salaryStructure->basic_salary;
        
        // Get attendance data
        $attendanceData = $this->getAttendanceData($employee->id, $formattedMonth);
        
        // Calculate per-day rate (30 days as per Nepal standard)
        $perDayRate = $basicSalary / 30;
        
        // Calculate absent days deduction (CORE FEATURE)
        $absentDays = $attendanceData['absent_days'] ?? 0;
        $absentDeduction = $absentDays * $perDayRate;
        
        // Determine salary year
        $salaryYear = Carbon::parse($formattedMonth . '-01')->year;
        
        // Get all active salary components
        $componentsQuery = SalaryComponent::active()->orderBy('sort_order');
        if ($employee) {
            $componentsQuery = $componentsQuery->applicableToEmployee($employee);
        }
        $components = $componentsQuery->get();
        
        // Initialize totals
        $totalAllowances = 0;
        $totalDeductions = 0;
        $componentsBreakdown = [];
        
        // Nepal standard allowances
        $dearnessAllowance = $employee->salaryStructure->dearness_allowance ?? 0;
        $houseRent = $employee->salaryStructure->house_rent_allowance ?? 0;
        $medicalAllowance = $employee->salaryStructure->medical_allowance ?? 0;
        $tiffinAllowance = $employee->salaryStructure->tiffin_allowance ?? 0;
        $transportAllowance = $employee->salaryStructure->transport_allowance ?? 0;
        $specialAllowance = $employee->salaryStructure->special_allowance ?? 0;
        
        // Add basic salary to breakdown
        $componentsBreakdown[] = [
            'name' => 'Basic Salary',
            'type' => 'allowance',
            'amount' => $basicSalary,
            'calculation_type' => 'fixed',
        ];
        
        // Add standard allowances
        $standardAllowances = [
            ['name' => 'Dearness Allowance', 'amount' => $dearnessAllowance],
            ['name' => 'House Rent Allowance', 'amount' => $houseRent],
            ['name' => 'Medical Allowance', 'amount' => $medicalAllowance],
            ['name' => 'Tiffin Allowance', 'amount' => $tiffinAllowance],
            ['name' => 'Transport Allowance', 'amount' => $transportAllowance],
            ['name' => 'Special Allowance', 'amount' => $specialAllowance],
        ];
        
        foreach ($standardAllowances as $allowance) {
            if ($allowance['amount'] > 0) {
                $componentsBreakdown[] = [
                    'name' => $allowance['name'],
                    'type' => 'allowance',
                    'amount' => $allowance['amount'],
                    'calculation_type' => 'fixed',
                ];
                $totalAllowances += $allowance['amount'];
            }
        }
        
        // Calculate custom components
        foreach ($components as $component) {
            $amount = $component->calculateAmount($employee, $basicSalary, $attendanceData);
            
            $componentsBreakdown[] = [
                'name' => $component->name,
                'type' => $component->type,
                'amount' => $amount,
                'calculation_type' => $component->calculation_type,
            ];
            
            if ($component->type === 'allowance') {
                $totalAllowances += $amount;
            } elseif ($component->type === 'deduction') {
                $totalDeductions += $amount;
            }
        }
        
        // Calculate overtime
        $overtimeHours = $this->getOvertimeHours($employee->id, $formattedMonth);
        // use employee-specific overtime rate; fall back to zero (no pay)
        $overtimeRate = $employee->salaryStructure->overtime_rate ?? 0;
        $overtimeAmount = $overtimeHours * $overtimeRate;
        
        if ($overtimeAmount > 0) {
            $componentsBreakdown[] = [
                'name' => 'Overtime',
                'type' => 'allowance',
                'amount' => $overtimeAmount,
                'calculation_type' => 'attendance_based',
            ];
            $totalAllowances += $overtimeAmount;
        }
        
        // Standard deductions
        $providentFund = $basicSalary * 0.10; // 10% PF
        $citizenInvestment = $employee->salaryStructure->citizen_investment ?? 0;
        
        if ($providentFund > 0) {
            $componentsBreakdown[] = [
                'name' => 'Provident Fund',
                'type' => 'deduction',
                'amount' => $providentFund,
                'calculation_type' => 'percentage',
            ];
            $totalDeductions += $providentFund;
        }
        
        if ($citizenInvestment > 0) {
            $componentsBreakdown[] = [
                'name' => 'Citizen Investment',
                'type' => 'deduction',
                'amount' => $citizenInvestment,
                'calculation_type' => 'fixed',
            ];
            $totalDeductions += $citizenInvestment;
        }
        
        // Income tax - USING DYNAMIC TAX CALCULATION
        $annualSalary = ($basicSalary + $totalAllowances) * 12;
        $isMarried = $employee->marital_status ?? false;
        $incomeTax = $this->calculateNepalTax($annualSalary, $isMarried) / 12;
        
        if ($incomeTax > 0) {
            $componentsBreakdown[] = [
                'name' => 'Income Tax',
                'type' => 'deduction',
                'amount' => $incomeTax,
                'calculation_type' => 'formula',
            ];
            $totalDeductions += $incomeTax;
        }
        
        // Late deduction (if enabled)
        $settings = SalarySetting::first();
        $lateDeduction = 0;
        if ($settings && $settings->enable_late_deduction) {
            $lateDeduction = $this->calculateLateDeduction($attendanceData, $basicSalary);
        }
        
        if ($lateDeduction > 0) {
            $componentsBreakdown[] = [
                'name' => 'Late Deduction',
                'type' => 'deduction',
                'amount' => $lateDeduction,
                'calculation_type' => 'attendance_based',
            ];
            $totalDeductions += $lateDeduction;
        }
        
        // Add absent deduction to total deductions
        if ($absentDeduction > 0) {
            $componentsBreakdown[] = [
                'name' => 'Absent Deduction',
                'type' => 'deduction',
                'amount' => $absentDeduction,
                'calculation_type' => 'attendance_based',
            ];
            $totalDeductions += $absentDeduction;
        }
        
        // Leave penalty
        $leavePenalty = $this->calculateLeavePenalty($employee->id, $formattedMonth, $basicSalary);
        
        if ($leavePenalty > 0) {
            $componentsBreakdown[] = [
                'name' => 'Leave Penalty',
                'type' => 'deduction',
                'amount' => $leavePenalty,
                'calculation_type' => 'attendance_based',
            ];
            $totalDeductions += $leavePenalty;
        }
        
        // Calculate final amounts
        $bonusAmount = 0;
        $grossSalary = $basicSalary + $totalAllowances + $bonusAmount;
        $netSalary = $grossSalary - $totalDeductions;
        
        // Prepare salary data - FIXED: salary_month is now properly formatted
        $salaryData = [
            'employee_id' => $employee->id,
            'salary_month' => $formattedMonth, // This is now just YYYY-MM (e.g., 2026-03)
            'basic_salary' => $basicSalary,
            'dearness_allowance' => $dearnessAllowance,
            'house_rent_allowance' => $houseRent,
            'medical_allowance' => $medicalAllowance,
            'tiffin_allowance' => $tiffinAllowance,
            'transport_allowance' => $transportAllowance,
            'special_allowance' => $specialAllowance,
            'overtime_rate' => $overtimeRate,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'provident_fund' => $providentFund,
            'citizen_investment' => $citizenInvestment,
            'income_tax' => $incomeTax,
            'penalty_leave_deduction' => $leavePenalty,
            'late_deduction_amount' => $lateDeduction,
            'absent_deduction_amount' => $absentDeduction,
            'absent_deduction' => $absentDeduction,
            'manual_fine' => 0,
            'manual_bonus' => 0,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,
            'insurance_amount' => 0,
            'bonus_amount' => 0,
            'late_deduction' => 0,
            'payment_status' => 'calculated',
            'calculated_by' => auth()->id(),
            'calculated_at' => now(),
            'attendance_summary' => json_encode($attendanceData),
            'components_breakdown' => json_encode($componentsBreakdown),
            'salary_year' => $salaryYear,
        ];

        // Filter by existing columns
        $salaryData = collect($salaryData)
            ->filter(fn($value, $key) => Schema::hasColumn('monthly_salaries', $key))
            ->toArray();

        // Create or update salary record
        $salary = MonthlySalary::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'salary_month' => $formattedMonth, // Use formatted month here too
            ],
            $salaryData
        );
        
        // Generate salary slip
        $this->generateSalarySlip($salary, $componentsBreakdown);
        
        return $salary;
    }
    
    /**
     * Apply manual fine to an employee
     */
    public function applyFine(Request $request)
    {
        $request->validate([
            'salary_id' => 'required|exists:monthly_salaries,id',
            'fine_amount' => 'required|numeric|min:0',
            'fine_reason' => 'required|string|max:500',
        ]);
        
        $salary = MonthlySalary::findOrFail($request->salary_id);
        
        DB::beginTransaction();
        
        try {
            // Update salary with manual fine
            $salary->manual_fine = $request->fine_amount;
            $salary->fine_reason = $request->fine_reason;
            
            // Recalculate totals
            $salary->total_deductions += $request->fine_amount;
            $salary->net_salary = $salary->gross_salary - $salary->total_deductions;
            
            $salary->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Fine applied successfully',
                'new_net_salary' => 'रु ' . number_format($salary->net_salary, 2)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error applying fine: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Apply manual bonus to an employee
     */
    public function applyBonus(Request $request)
    {
        $request->validate([
            'salary_id' => 'required|exists:monthly_salaries,id',
            'bonus_amount' => 'required|numeric|min:0',
            'bonus_reason' => 'required|string|max:500',
        ]);
        
        $salary = MonthlySalary::findOrFail($request->salary_id);
        
        DB::beginTransaction();
        
        try {
            // Update salary with manual bonus
            $salary->manual_bonus = $request->bonus_amount;
            $salary->bonus_reason = $request->bonus_reason;
            
            // Recalculate totals
            $salary->total_allowances += $request->bonus_amount;
            $salary->gross_salary = $salary->basic_salary + $salary->total_allowances;
            $salary->net_salary = $salary->gross_salary - $salary->total_deductions;
            
            $salary->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bonus applied successfully',
                'new_net_salary' => 'रु ' . number_format($salary->net_salary, 2)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error applying bonus: ' . $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Get attendance data with correct absent days calculation
     */
    private function getAttendanceData($employeeId, $month)
    {
        $startDate = Carbon::parse($month . '-01');
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get attendance records
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Calculate totals
        $presentDays = $attendance->where('status', 'present')->count();
        $leaveDays = $attendance->where('status', 'leave')->count();
        $lateDays = $attendance->where('is_late', true)->count();
        $totalLateMinutes = $attendance->sum('late_minutes');
        $overtimeHours = $attendance->sum('overtime');
        
        $totalDaysInMonth = $endDate->diffInDays($startDate) + 1;
        $totalDaysInMonth = max(1, $totalDaysInMonth); // Ensure at least 1 day
        
        // Absent days = total days - present - leave
        $absentDays = $totalDaysInMonth - $presentDays - $leaveDays;
        $absentDays = max(0, $absentDays); // Never negative
        
        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'late_days' => $lateDays,
            'total_late_minutes' => max(0, $totalLateMinutes),
            'overtime_hours' => max(0, $overtimeHours),
            'working_days' => $totalDaysInMonth,
        ];
    }
    
    /**
     * Calculate late deduction
     */
    private function calculateLateDeduction($attendanceData, $basicSalary)
    {
        $lateMinutes = $attendanceData['total_late_minutes'] ?? 0;
        
        // Check if there's a custom component
        $lateComponent = SalaryComponent::where('attendance_field', 'late_minutes')
            ->where('type', 'deduction')
            ->active()
            ->first();
        
        if ($lateComponent && $lateComponent->attendance_rate) {
            $lateHours = $lateMinutes / 60;
            return $lateHours * $lateComponent->attendance_rate;
        }
        
        // Default: No late deduction
        return 0;
    }
    
    private function getOvertimeHours($employeeId, $month)
    {
        $data = $this->getAttendanceData($employeeId, $month);
        return $data['overtime_hours'] ?? 0;
    }
    
    private function calculateLeavePenalty($employeeId, $month, $basicSalary)
    {
        $startDate = Carbon::parse($month . '-01');
        $endDate = $startDate->copy()->endOfMonth();
        $perDayRate = $basicSalary / 30;
        
        // Get unauthorized leaves
        $unauthorizedLeaves = LeaveRecord::where('employee_id', $employeeId)
            ->where('status', '!=', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->get();
        
        $unauthorizedDays = 0;
        foreach ($unauthorizedLeaves as $leave) {
            $start = max($leave->start_date, $startDate);
            $end = min($leave->end_date, $endDate);
            $unauthorizedDays += $start->diffInDays($end) + 1;
        }
        
        return $unauthorizedDays * $perDayRate;
    }
    
    /**
     * Nepal Tax Calculation FY 2082/83 - DYNAMIC
     */
    private function calculateNepalTax($annualSalary, $isMarried = false)
    {
        // Get active tax settings
        $taxSetting = TaxSetting::where('is_active', true)->first();
        
        if (!$taxSetting) {
            // Default calculation if no settings
            return $this->calculateDefaultNepalTax($annualSalary, $isMarried);
        }
        
        $slabs = $isMarried ? $taxSetting->married_slabs : $taxSetting->unmarried_slabs;
        
        if (empty($slabs)) {
            return $this->calculateDefaultNepalTax($annualSalary, $isMarried);
        }
        
        $tax = 0;
        $remainingIncome = $annualSalary;
        
        foreach ($slabs as $slab) {
            if ($remainingIncome <= 0) break;
            
            $from = $slab['from'] ?? 0;
            $to = $slab['to'] ?? PHP_FLOAT_MAX;
            $rate = ($slab['rate'] ?? 0) / 100;
            $fixed = $slab['fixed'] ?? 0;
            
            if ($remainingIncome > $from) {
                $slabAmount = min($remainingIncome, $to - $from);
                $tax += ($slabAmount * $rate) + $fixed;
                $remainingIncome -= $slabAmount;
            }
        }
        
        return round($tax, 2);
    }
    
    /**
     * Default Nepal tax calculation (fallback)
     */
    private function calculateDefaultNepalTax($annualSalary, $isMarried = false)
    {
        $tax = 0;
        
        if ($isMarried) {
            // Married slabs
            if ($annualSalary <= 600000) {
                $tax = $annualSalary * 0.01;
            } elseif ($annualSalary <= 800000) {
                $tax = 6000 + ($annualSalary - 600000) * 0.10;
            } elseif ($annualSalary <= 1100000) {
                $tax = 26000 + ($annualSalary - 800000) * 0.20;
            } elseif ($annualSalary <= 2100000) {
                $tax = 86000 + ($annualSalary - 1100000) * 0.30;
            } elseif ($annualSalary <= 5100000) {
                $tax = 386000 + ($annualSalary - 2100000) * 0.36;
            } else {
                $tax = 1466000 + ($annualSalary - 5100000) * 0.39;
            }
        } else {
            // Unmarried slabs
            if ($annualSalary <= 500000) {
                $tax = $annualSalary * 0.01;
            } elseif ($annualSalary <= 700000) {
                $tax = 5000 + ($annualSalary - 500000) * 0.10;
            } elseif ($annualSalary <= 1000000) {
                $tax = 25000 + ($annualSalary - 700000) * 0.20;
            } elseif ($annualSalary <= 2000000) {
                $tax = 85000 + ($annualSalary - 1000000) * 0.30;
            } elseif ($annualSalary <= 5000000) {
                $tax = 385000 + ($annualSalary - 2000000) * 0.36;
            } else {
                $tax = 1465000 + ($annualSalary - 5000000) * 0.39;
            }
        }
        
        return round($tax, 2);
    }
    
    private function generateSalarySlip($salary, $componentsBreakdown)
    {
        // Check if slip already exists
        $existingSlip = SalarySlip::where('salary_id', $salary->id)->first();
        if ($existingSlip) {
            return $existingSlip;
        }
        
        // Create new slip
        return SalarySlip::create([
            'salary_id' => $salary->id,
            'slip_number' => SalarySlip::generateSlipNumber(),
            'components_breakdown' => $componentsBreakdown,
            'attendance_summary' => $salary->attendance_summary ?? [],
            'tax_calculation' => [
                'annual_income' => ($salary->gross_salary * 12),
                'tax_amount' => $salary->income_tax,
                // avoid division by zero; if gross salary is zero there can't be a
            // meaningful percentage, so just return 0.
            'tax_percentage' => ($salary->gross_salary > 0 && $salary->income_tax > 0)
                                ? ($salary->income_tax / $salary->gross_salary) * 100
                                : 0,
            ],
            'issued_by' => auth()->user()->name,
            'issue_date' => now(),
            'payment_method' => 'Bank Transfer',
            'bank_name' => $salary->employee->bank_name,
            'account_number' => $salary->employee->account_number,
        ]);
    }
    
    public function history(Request $request)
    {
        $query = MonthlySalary::with('employee');
        
        if ($request->employee) {
            $query->where('employee_id', $request->employee);
        }
        
        if ($request->year) {
            // match any month in the requested year
            $query->where('salary_month', 'like', $request->year . '-%');
        }
        
        if ($request->month) {
            // make sure month is zero-padded
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $query->where('salary_month', $request->year . '-' . $month);
        }
        
        if ($request->status) {
            $query->where('payment_status', $request->status);
        }
        
        $salaries = $query->orderBy('salary_month', 'desc')
                         ->orderBy('employee_id')
                         ->paginate(20);
        
        $allEmployees = Employee::active()->get();
        
        return view('salary.history', compact('salaries', 'allEmployees'));
    }
    
    public function edit($id)
    {
        $salary = MonthlySalary::with('employee')->findOrFail($id);
        return view('salary.edit', compact('salary'));
    }
    
    public function update(Request $request, $id)
    {
        $salary = MonthlySalary::findOrFail($id);
        
        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'dearness_allowance' => 'numeric|min:0',
            'house_rent_allowance' => 'numeric|min:0',
            'medical_allowance' => 'numeric|min:0',
            'overtime_hours' => 'numeric|min:0',
            'provident_fund' => 'numeric|min:0',
            'citizen_investment' => 'numeric|min:0',
            'income_tax' => 'numeric|min:0',
            'penalty_leave_deduction' => 'numeric|min:0',
            'late_deduction' => 'numeric|min:0',
            'manual_fine' => 'nullable|numeric|min:0',
            'manual_bonus' => 'nullable|numeric|min:0',
            'fine_reason' => 'nullable|string',
            'bonus_reason' => 'nullable|string',
            // allowed statuses now include hold & cancelled in addition to
            // the ones used by calculation logic
            'payment_status' => 'required|in:pending,calculated,paid,hold,cancelled',
            'remarks' => 'nullable|string',
        ]);
        
        // Calculate overtime amount
        $overtimeRate = $salary->employee->salaryStructure->overtime_rate ?? 0;
        $overtimeAmount = ($validated['overtime_hours'] ?? 0) * $overtimeRate;
        
        // Calculate totals including manual adjustments
        $totalAllowances = 
            ($validated['dearness_allowance'] ?? 0) +
            ($validated['house_rent_allowance'] ?? 0) +
            ($validated['medical_allowance'] ?? 0) +
            $overtimeAmount +
            ($validated['manual_bonus'] ?? 0);
        
        $totalDeductions = 
            ($validated['provident_fund'] ?? 0) +
            ($validated['citizen_investment'] ?? 0) +
            ($validated['income_tax'] ?? 0) +
            ($validated['penalty_leave_deduction'] ?? 0) +
            ($validated['late_deduction'] ?? 0) +
            ($validated['manual_fine'] ?? 0);
        
        $validated['overtime_amount'] = $overtimeAmount;
        $validated['total_allowances'] = $totalAllowances;
        $validated['total_deductions'] = $totalDeductions;
        $validated['gross_salary'] = $validated['basic_salary'] + $totalAllowances;
        $validated['net_salary'] = ($validated['basic_salary'] + $totalAllowances) - $totalDeductions;
        
        if ($validated['payment_status'] == 'paid' && $salary->payment_status != 'paid') {
            $validated['paid_by'] = auth()->id();
            $validated['paid_at'] = now();
            $validated['payment_date'] = now();
        }
        
        // Filter by existing columns
        $validated = collect($validated)
            ->filter(fn($value, $key) => Schema::hasColumn('monthly_salaries', $key))
            ->toArray();

        $salary->update($validated);
        
        return redirect()->route('salary.index')
            ->with('success', 'Salary updated successfully!');
    }
    
    /**
     * Show a simple payslip view generated from the salary record.  This is
     * mostly used by accountants to quickly print or download a PDF for the
     * employee.
     */
    public function payslip($id)
    {
        $salary = MonthlySalary::with(['employee', 'employee.departments'])->findOrFail($id);
        return view('salary.payslip', compact('salary'));
    }

    /**
     * Display the payout form for a given salary record.  The form shows the
     * employee and salary breakdown along with inputs for cheque/bank details
     * and allows the accountant to mark the salary as paid.
     */
    public function payoutForm($id)
    {
        $salary = MonthlySalary::with(['employee', 'employee.departments'])->findOrFail($id);        // only allow payout for closed months
        if ($salary->salary_month >= Carbon::now()->format('Y-m')) {
            return redirect()->route('salary.index', ['month' => $salary->salary_month])
                ->with('error', 'Cannot mark payout for salary of an open month');
        }        return view('salary.payout', compact('salary'));
    }

    /**
     * Handle submission of the payout form.
     */
    public function processPayout(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string|max:50',
            'payment_bank' => 'required|string|max:100',
            'cheque_number' => 'nullable|string|max:100',
            'paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $salary = MonthlySalary::findOrFail($id);
        if ($salary->salary_month >= Carbon::now()->format('Y-m')) {
            return redirect()->route('salary.index', ['month' => $salary->salary_month])
                ->with('error', 'Cannot pay salary for an open month');
        }

        $updateData = [
            'payment_status' => 'paid',
            'payment_method' => $request->payment_method,
            'payment_bank' => $request->payment_bank,
            'cheque_number' => $request->cheque_number,
            'paid_amount' => $request->paid_amount,
            'payment_date' => $request->payment_date,
        ];

        if (Schema::hasColumn('monthly_salaries', 'paid_by')) {
            $updateData['paid_by'] = auth()->id();
        }
        if (Schema::hasColumn('monthly_salaries', 'paid_at')) {
            $updateData['paid_at'] = now();
        }

        $salary->update($updateData);

        return redirect()->route('salary.history')
            ->with('success', 'Salary marked as paid and payout details recorded.');
    }

    public function markAsPaid($id)
    {
        $salary = MonthlySalary::findOrFail($id);

        $updateData = ['payment_status' => 'paid'];
        if (Schema::hasColumn('monthly_salaries', 'paid_by')) {
            $updateData['paid_by'] = auth()->id();
        }
        if (Schema::hasColumn('monthly_salaries', 'paid_at')) {
            $updateData['paid_at'] = now();
        }
        if (Schema::hasColumn('monthly_salaries', 'payment_date')) {
            $updateData['payment_date'] = now();
        }

        $salary->update($updateData);
        
        return response()->json(['success' => true]);
    }
}