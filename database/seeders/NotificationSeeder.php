<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample users to send notifications to
        $users = User::whereIn('role', ['admin', 'hr', 'employee'])->take(5)->get();
        $admin = User::where('role', 'admin')->first();

        if (!$admin) return;

        foreach ($users as $user) {
            // Sample notifications
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to Payroll System',
                'message' => 'Welcome! You now have access to the payroll management system. Check your profile to get started.',
                'type' => 'general',
                'created_by' => $admin->id,
            ]);

            Notification::create([
                'user_id' => $user->id,
                'title' => 'New Salary Slip Available',
                'message' => 'Your salary slip for January 2026 is now available. You can download it from the Salary section.',
                'type' => 'salary',
                'related_model' => 'MonthlySalary',
                'created_by' => $admin->id,
            ]);

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Attendance Record Updated',
                'message' => 'Your attendance for today has been recorded. Check the Attendance section for details.',
                'type' => 'attendance',
                'related_model' => 'Attendance',
                'created_by' => $admin->id,
            ]);
        }

        $this->command->info('✅ Notification seeder completed!');
    }
}
