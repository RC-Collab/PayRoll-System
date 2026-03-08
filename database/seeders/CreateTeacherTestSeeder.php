<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class CreateTeacherTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create user
        $user = User::updateOrCreate(
            ['email' => 'nikki@payroll.com'],
            [
                'name' => 'nikki',
                'password' => Hash::make('nikki123'),
                'role' => 'teacher',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('User: ' . $user->email . ' (ID: ' . $user->id . ')');

        // Check if employee already exists
        if (Employee::where('email', 'nikki@payroll.com')->exists()) {
            $this->command->info('Employee already exists for this email. Skipping...');
            return;
        }

        // Create Employee linked to the user
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-NIKKI-' . strtoupper(substr(md5(time()), 0, 6)),
            'first_name' => 'Nikki',
            'last_name' => 'Teacher',
            'email' => 'nikki@payroll.com',
            'mobile_number' => '9800000000',
            'gender' => 'female',
            'date_of_birth' => '1995-01-15',
            'joining_date' => now()->format('Y-m-d'),
            'employee_type' => 'permanent',
            'designation' => 'Teacher',
            'employment_status' => 'active',
            'bank_name' => 'Test Bank',
            'account_number' => '0000000000',
            'account_holder_name' => 'Nikki Teacher',
            'branch_name' => 'Test Branch',
            'is_active' => true,
        ]);

        $this->command->info('Employee created: ' . $employee->first_name . ' ' . $employee->last_name);
        $this->command->info('Employee ID: ' . $employee->id);
        $this->command->info('Login credentials:');
        $this->command->info('  Email: nikki@payroll.com');
        $this->command->info('  Password: nikki123');
        $this->command->info('  Role: teacher');
    }
}
