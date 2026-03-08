<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorized by LeaveController middleware
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'leave_type' => 'required|string|in:sick,casual,annual,maternity,paternity,bereavement,unpaid',
            'start_date' => 'required|date|after_or_equal:today|date_format:Y-m-d',
            'end_date' => 'required|date|after_or_equal:start_date|date_format:Y-m-d',
            'reason' => 'required|string|min:10|max:500',
            'contact_during_leave' => 'nullable|string|max:20',
            'contact_number' => 'nullable|string|max:20',
            'alternate_contact' => 'nullable|string|max:100',
            'handover_notes' => 'nullable|string|max:1000',
            'medical_certificate' => 'nullable|boolean',
            'is_half_day' => 'nullable|boolean',
            'half_day_period' => 'nullable|string|in:first_half,second_half',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee selection is required.',
            'leave_type.required' => 'Leave type is required.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'reason.required' => 'Reason for leave is required.',
            'reason.min' => 'Reason must be at least 10 characters.',
        ];
    }
}
