<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\Api\AttendanceApiController;

class MarkAbsent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * date is optional; defaults to yesterday (the day we want to finalize).
     */
    protected $signature = 'attendance:mark-absent {date? : The date to mark absences for (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For a working day, create absent records for any employee without attendance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date   = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::yesterday();
        $dateString = $date->toDateString();

        if (!AttendanceApiController::isWorkingDay($date)) {
            $this->info("{$dateString} is not a working day, nothing to do.");
            return 0;
        }

        $employees = Employee::all();
        $created = 0;
        foreach ($employees as $employee) {
            $exists = Attendance::where('employee_id', $employee->id)
                        ->where('date', $dateString)
                        ->exists();
            if (!$exists) {
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date'        => $dateString,
                    'status'      => 'absent',
                ]);
                $created++;
            }
        }

        $this->info("Absences marked for {$dateString} ({$created} employees)");
        return 0;
    }
}
