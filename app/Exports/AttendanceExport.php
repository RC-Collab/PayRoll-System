<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;
    
    public function __construct($request)
    {
        $this->request = $request;
    }
    
    public function collection()
    {
        $query = Attendance::with('employee.department');
        
        if ($this->request->has('month')) {
            $query->whereMonth('date', Carbon::parse($this->request->month)->month)
                  ->whereYear('date', Carbon::parse($this->request->month)->year);
        }
        
        return $query->get();
    }
    
    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Department',
            'Date',
            'Status',
            'Check In',
            'Check Out',
            'Total Hours',
            'Notes'
        ];
    }
    
    public function map($attendance): array
    {
        return [
            $attendance->employee->employee_id,
            $attendance->employee->full_name,
            $attendance->employee->department->name ?? '-',
            $attendance->date->format('d-M-Y'),
            ucfirst($attendance->status),
            $attendance->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : '-',
            $attendance->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : '-',
            $attendance->total_hours ?? '-',
            $attendance->notes ?? '-',
        ];
    }
}