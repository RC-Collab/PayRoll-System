<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SalaryResource;
use App\Models\MonthlySalary;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalaryApiController extends Controller
{
    /**
     * GET /api/salary/monthly
     * Get monthly salary
     */
    public function monthly(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $month = $request->query('month', now()->month);
            $year = $request->query('year', now()->year);

            // month/year are integers; convert to "YYYY-MM"
            $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
            $salary = MonthlySalary::where('employee_id', $employee->id)
                ->where('salary_month', $year . '-' . $monthPadded)
                ->first();

            if (!$salary) {
                return response()->json([
                    'message' => 'No salary record found for this month',
                ], 404);
            }

            return response()->json([
                'message' => 'Monthly salary retrieved successfully',
                'data' => new SalaryResource($salary),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve salary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/salary/status
     * Get salary payment status (yearly overview of months)
     */
    public function status(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $year = $request->query('year', now()->year);

            $salaries = MonthlySalary::where('employee_id', $employee->id)
                ->where('salary_month', 'like', $year . '-%')
                ->orderBy('salary_month', 'desc')
                ->get();

            if ($salaries->isEmpty()) {
                return response()->json([
                    'message' => 'No salary records found for this year',
                ], 404);
            }

            $paymentStatus = $salaries->map(function ($salary) {
                return [
                    'month' => $salary->salary_month->format('Y-m'),
                    'month_name' => $salary->salary_month->format('F Y'),
                    'net_salary' => (float) $salary->net_salary,
                    'payment_status' => $salary->payment_status,
                    'payment_date' => $salary->payment_date?->format('Y-m-d'),
                    'payment_method' => $salary->payment_method,
                    'payment_bank' => $salary->payment_bank,
                    'cheque_number' => $salary->cheque_number,
                    'paid_amount' => $salary->paid_amount,
                    'transaction_reference' => $salary->transaction_reference,
                ];
            });

            return response()->json([
                'message' => 'Salary payment status retrieved successfully',
                'year' => $year,
                'data' => $paymentStatus,
                'summary' => [
                    'total_months' => $salaries->count(),
                    'paid' => $salaries->where('payment_status', 'paid')->count(),
                    'pending' => $salaries->where('payment_status', 'pending')->count(),
                    'total_amount' => (float) $salaries->sum('net_salary'),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve salary status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/salary/history
     * Get a paginated list of salary records for the authenticated employee.
     * Query params: year, month (optional) + page
     */
    public function history(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $year = $request->query('year', now()->year);
            $month = $request->query('month');

            $query = MonthlySalary::where('employee_id', $employee->id)
                ->where('salary_month', 'like', $year . '-%');

            if ($month) {
                $query->where('salary_month', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));
            }

            $salaries = $query->orderBy('salary_month', 'desc')
                               ->paginate(10);

            if ($salaries->isEmpty()) {
                return response()->json([
                    'message' => 'No salary records found',
                ], 404);
            }

            // build the resource collection and extract its underlying array
            $resources = SalaryResource::collection($salaries);
            $resourcePayload = $resources->response()->getData(true);

            // keep the entire resource payload nested under a top-level 'data'
            $payload = [
                'message' => 'Salary history retrieved successfully',
                'data' => $resourcePayload,
                'summary' => [
                    'total' => $salaries->total(),
                    'paid' => $salaries->where('payment_status', 'paid')->count(),
                    'pending' => $salaries->where('payment_status', 'pending')->count(),
                ],
                'pagination' => [
                    'current_page' => $salaries->currentPage(),
                    'last_page' => $salaries->lastPage(),
                ],
            ];

            return response()->json($payload, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve salary history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/salary/receipt/{id}
     * Get salary slip/receipt
     */
    public function receipt(Request $request, $id)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            $salary = MonthlySalary::find($id);

            if (!$salary) {
                return response()->json([
                    'message' => 'Salary record not found',
                ], 404);
            }

            // Check ownership
            if ($salary->employee_id !== $employee->id) {
                return response()->json([
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $receiptData = [
                'employee' => [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'email' => $employee->email,
                    'designation' => $employee->designation,
                    'bank_account' => $employee->account_number,
                ],
                'salary_month' => $salary->salary_month->format('F Y'),
                    'salary_year' => $salary->salary_year ?? $salary->salary_month->year,
                'earnings' => [
                    'basic_salary' => (float) ($salary->basic_salary ?? 0),
                    'dearness_allowance' => (float) ($salary->dearness_allowance ?? 0),
                    'house_rent_allowance' => (float) ($salary->house_rent_allowance ?? 0),
                    'medical_allowance' => (float) ($salary->medical_allowance ?? 0),
                    'tiffin_allowance' => (float) ($salary->tiffin_allowance ?? 0),
                    'transport_allowance' => (float) ($salary->transport_allowance ?? 0),
                    'special_allowance' => (float) ($salary->special_allowance ?? 0),
                    'overtime_amount' => (float) ($salary->overtime_amount ?? 0),
                    'bonus_amount' => (float) ($salary->bonus_amount ?? 0),
                ],
                'deductions' => [
                    'provident_fund' => (float) ($salary->provident_fund ?? 0),
                    'citizen_investment' => (float) ($salary->citizen_investment ?? 0),
                    'income_tax' => (float) ($salary->income_tax ?? 0),
                    'insurance_amount' => (float) ($salary->insurance_amount ?? 0),
                    'late_deduction' => (float) ($salary->late_deduction_amount ?? 0),
                    'absent_deduction' => (float) ($salary->absent_deduction_amount ?? 0),
                    'advance_deduction' => (float) ($salary->advance_deduction ?? 0),
                    'penalty_deduction' => (float) ($salary->penalty_leave_deduction ?? 0),
                ],
                'totals' => [
                    'total_allowances' => (float) ($salary->total_allowances ?? 0),
                    'total_deductions' => (float) ($salary->total_deductions ?? 0),
                    'gross_salary' => (float) ($salary->gross_salary ?? 0),
                    'net_salary' => (float) ($salary->net_salary ?? 0),
                ],
                'attendance' => [
                    'working_days' => $salary->working_days,
                    'present_days' => $salary->present_days,
                    'absent_days' => $salary->absent_days,
                    'leave_days' => $salary->leave_days,
                    'overtime_hours' => $salary->overtime_hours,
                ],
                'payment' => [
                    'status' => $salary->payment_status,
                    'date' => $salary->payment_date?->format('Y-m-d'),
                    'method' => $salary->payment_method,
                    'transaction_ref' => $salary->transaction_reference,
                ],
            ];

            return response()->json([
                'message' => 'Salary receipt retrieved successfully',
                'data' => $receiptData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve salary receipt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
