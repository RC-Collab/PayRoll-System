<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Models\TaxConfiguration;
use Illuminate\Support\Facades\Schema;

class NepalPayrollSeeder extends Seeder
{
    public function run()
    {
        echo "Starting Nepal Payroll Seeder...\n";

        // 1. Create Department
        echo "Creating department...\n";
        $department = Department::firstOrCreate(
            ['code' => 'ACAD'],
            [
                'name' => 'Academic Department',
                'description' => 'Teaching faculty and academic staff',
                'category' => 'academic',
                'head_of_department' => 'Dr. Sharma',
                'icon' => '🎓',
                'roles' => json_encode(['Teacher', 'Professor', 'Lecturer', 'Lab Assistant']),
                'is_active' => true,
            ]
        );

        // 2. Create Tax Configuration
        echo "Creating tax configuration...\n";
        TaxConfiguration::firstOrCreate(
            ['fiscal_year' => '2080/81'],
            [
                'individual_tax_free_limit' => 500000,
                'tax_slabs' => json_encode([
                    ['from' => 0, 'to' => 500000, 'rate' => 0, 'fixed_amount' => 0],
                    ['from' => 500000, 'to' => 700000, 'rate' => 10, 'fixed_amount' => 0],
                    ['from' => 700000, 'to' => 1000000, 'rate' => 20, 'fixed_amount' => 20000],
                    ['from' => 1000000, 'to' => 2000000, 'rate' => 30, 'fixed_amount' => 90000],
                    ['from' => 2000000, 'to' => null, 'rate' => 36, 'fixed_amount' => 390000]
                ]),
                'provident_fund_percentage' => 10,
                'citizen_investment_percentage' => 0,
                'is_active' => true,
            ]
        );

        // 3. Check if employee already exists
        echo "Checking for existing employee...\n";
        if (Employee::where('email', 'ram.shrestha@college.edu.np')->exists()) {
            echo "Employee already exists. Skipping...\n";
            return;
        }

        // 4. Prepare employee data
        echo "Preparing employee data...\n";
        $employeeData = [
            'employee_code' => 'EMP2080-001',
            'first_name' => 'Ram',
            'last_name' => 'Shrestha',
            'email' => 'ram.shrestha@college.edu.np',
            'phone' => '9841234567',
            'date_of_birth' => '1990-05-15',
            'gender' => 'male',
            'marital_status' => 'married',
            'joining_date' => '2021-04-14',
            'position_title' => 'Professor',
            'is_active' => true,
        ];

        // 5. Add additional fields only if they exist in table
        $existingColumns = Schema::getColumnListing('employees');
        
        $optionalFields = [
            'middle_name' => 'Bahadur',
            'mobile_number' => '9841234567',
            'blood_group' => 'B+',
            'citizenship_number' => '12345-067-08901',
            'pan_number' => '602345678',
            'current_address' => 'Kumaripati, Lalitpur',
            'permanent_address' => 'Salyan, Salyan',
            'district' => 'Lalitpur',
            'emergency_contact_name' => 'Sita Shrestha',
            'emergency_contact_phone' => '9849876543',
            'emergency_contact_relation' => 'Wife',
            'qualification' => 'M.Ed.',
            'institution_name' => 'Tribhuvan University',
            'experience_years' => 8,
            'bank_name' => 'Nepal Bank',
            'account_number' => '123456789012',
            'account_holder_name' => 'Ram Bahadur Shrestha',
            'branch_name' => 'Pulchowk Branch',
            'department' => 'Academic',
            'designation' => 'Professor',
            'nationality' => 'Nepali',
            'employment_status' => 'active',
            'employee_type' => 'permanent',
            'employment_type' => 'Full-time',
        ];

        foreach ($optionalFields as $field => $value) {
            if (in_array($field, $existingColumns)) {
                $employeeData[$field] = $value;
            }
        }

        // 6. Create employee
        echo "Creating employee...\n";
        try {
            $employee = Employee::create($employeeData);
            echo "Employee created successfully: " . $employee->name . "\n";
        } catch (\Exception $e) {
            echo "Error creating employee: " . $e->getMessage() . "\n";
            return;
        }

        // 7. Create salary structure
        echo "Creating salary structure...\n";
        SalaryStructure::create([
            'employee_id' => $employee->id,
            'basic_salary' => 60000,
            'dearness_allowance' => 12000,
            'house_rent_allowance' => 15000,
            'medical_allowance' => 2000,
            'tiffin_allowance' => 3000,
            'transport_allowance' => 5000,
            'special_allowance' => 10000,
            'overtime_rate_per_hour' => 500,
            'provident_fund_percentage' => 10,
            'citizen_investment_percentage' => 0,
            'salary_grade' => 'Grade 8',
            'salary_scale' => '8-1',
            'effective_from' => '2023-01-01',
            'is_current' => true,
        ]);

        // 8. Assign to department
        echo "Assigning to department...\n";
        $department->employees()->attach($employee->id, [
            'role' => 'Professor',
            'is_primary' => true,
            'start_date' => '2021-04-14'
        ]);

        echo "Nepal Payroll Seeder completed successfully!\n";
    }
}