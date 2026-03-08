<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ApplyLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'leave_type' => 'required|string|in:sick_leave,casual_leave,annual_leave,maternity_leave,paternity_leave,study_leave,bereavement_leave,unpaid_leave',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string|max:1000',
            'contact_during_leave' => 'nullable|string|max:50',
            'medical_certificate' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'leave_type.required' => 'Leave type is required',
            'leave_type.in' => 'Invalid leave type',
            'start_date.required' => 'Start date is required',
            'start_date.date_format' => 'Start date must be in Y-m-d format',
            'end_date.required' => 'End date is required',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'total_days.required' => 'Total days is required',
            'reason.required' => 'Reason is required',
        ];
    }
}
