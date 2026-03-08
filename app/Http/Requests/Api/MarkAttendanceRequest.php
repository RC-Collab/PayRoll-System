<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MarkAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date',
            // allow extended statuses that appear in the quick‑mark UI
            'status' => 'required|in:present,absent,overtime,leave,half_day',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'dates.required' => 'At least one date is required',
            'dates.*.date_format' => 'Date must be in Y-m-d format',
            'attendance_type.in' => 'Invalid attendance type',
            'status.in' => 'Invalid status',
        ];
    }
}
