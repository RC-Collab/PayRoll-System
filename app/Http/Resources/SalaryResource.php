<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'salary_month' => $this->salary_month->format('Y-m'),
            // some installations keep a separate year column
            'salary_year' => $this->salary_year ?? $this->salary_month->year,
            'working_days' => $this->working_days,
            'present_days' => $this->present_days,
            'absent_days' => $this->absent_days,
            'leave_days' => $this->leave_days,
            'basic_salary' => (float) $this->basic_salary,
            'total_allowances' => (float) $this->total_allowances,
            'total_deductions' => (float) $this->total_deductions,
            'gross_salary' => (float) $this->gross_salary,
            'net_salary' => (float) $this->net_salary,
            'payment_status' => $this->payment_status,
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'payment_bank' => $this->payment_bank,
            'cheque_number' => $this->cheque_number,
            'paid_amount' => $this->paid_amount,
            'transaction_reference' => $this->transaction_reference,
            'remarks' => $this->remarks,
        ];
    }
}
