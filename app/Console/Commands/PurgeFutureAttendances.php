<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgeFutureAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Optional cutoff date (deletes strictly greater than this date). Defaults to today.
     */
    protected $signature = 'attendance:purge-future {cutoff? : Date up to which future attendances will be deleted (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove any attendance records whose date is after the given cutoff (today by default)';

    public function handle()
    {
        $cutoff = $this->argument('cutoff') ? Carbon::parse($this->argument('cutoff')) : Carbon::today();
        $cutoffDate = $cutoff->toDateString();

        $count = Attendance::whereDate('date', '>', $cutoffDate)->count();
        Attendance::whereDate('date', '>', $cutoffDate)->delete();

        $this->info("Purged {$count} attendance record(s) newer than {$cutoffDate}");

        return 0;
    }
}
