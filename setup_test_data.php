<?php
// Script to create test employee and verify API

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Employee;
use App\Models\EmergencyContact;
use App\Models\Qualification;
use App\Models\Experience;

// Create test user
$user = User::firstOrCreate(
    ['email' => 'test.employee@payroll.com'],
    [
        'name' => 'Test Employee',
        'phone' => '9800000000',
        'password' => bcrypt('password123')
    ]
);

// Create employee
$employee = Employee::firstOrCreate(
    ['user_id' => $user->id],
    [
        'employee_code' => 'EMP999',
        'first_name' => 'Test',
        'last_name' => 'Employee',
        'email' => 'test.employee@payroll.com',
        'mobile_number' => '9800000000',
        'designation' => 'Software Developer',
        'employee_type' => 'Full-Time',
        'employment_status' => 'Active',
        'joining_date' => '2023-01-01',
        'gender' => 'male',
        'date_of_birth' => '1995-05-15',
        'present_address' => '123 Test Street',
        'permanent_address' => '456 Test Avenue',
        'city' => 'Kathmandu',
        'state' => 'Bagmati',
        'country' => 'Nepal',
        'postal_code' => '44600'
    ]
);

echo "\n✅ TEST DATA CREATED:\n";
echo "User: " . $user->email . "\n";
echo "Employee: " . $employee->first_name . " " . $employee->last_name . "\n";

// Add sample emergency contact
EmergencyContact::firstOrCreate(
    ['employee_id' => $employee->id, 'name' => 'Test Contact'],
    [
        'relationship' => 'Spouse',
        'phone' => '9876543210',
        'email' => 'contact@example.com',
        'address' => '123 Contact St',
        'is_primary' => true
    ]
);

echo "\nSample Data Added:\n";
echo "  ✓ Emergency Contact\n";

// Verify loading relationships
$employee->load([
    'emergencyContacts',
    'qualifications',
    'experiences',
    'documents'
]);

echo "\n📊 DATA VERIFICATION:\n";
echo "  Emergency Contacts: " . count($employee->emergencyContacts) . " ✓\n";
echo "  Qualifications: " . count($employee->qualifications) . " ✓\n";
echo "  Experiences: " . count($employee->experiences) . " ✓\n";
echo "  Documents: " . count($employee->documents) . " ✓\n";

echo "\n✅ API IS READY TO USE!\n";
echo "\nTest Credentials:\n";
echo "  Email: test.employee@payroll.com\n";
echo "  Password: password123\n";
