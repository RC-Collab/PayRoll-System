<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\MarkAbsent::class,
        \App\Console\Commands\PurgeFutureAttendances::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Every night just before midnight check for missing attendances.
        $schedule->command('attendance:mark-absent')->dailyAt('23:59');

        // purge any stray future records once a day as a safety net
        $schedule->command('attendance:purge-future')->dailyAt('00:05');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
