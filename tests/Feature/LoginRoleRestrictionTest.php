<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class LoginRoleRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // create some departments (factory not available)
        Department::create(['id' => 1, 'name' => 'Administrative']);
        Department::create(['id' => 2, 'name' => 'Sales']);
    }

    public function test_admin_can_login()
    {
        $user = User::factory()->create([ 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true, 'email_verified_at' => now() ]);

        $response = $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_view_employee_create_form()
    {
        $admin = User::factory()->create([ 'role' => 'admin', 'is_active' => true, 'email_verified_at' => now() ]);
        $this->actingAs($admin);

        $response = $this->get('/employees/create');
        $response->assertStatus(200);
        $response->assertViewIs('employees.create');
    }

    public function test_hr_can_login()
    {
        $user = User::factory()->create([ 'email' => 'hr@example.com', 'password' => Hash::make('password'), 'role' => 'hr', 'is_active' => true, 'email_verified_at' => now() ]);

        $response = $this->post('/login', ['email' => 'hr@example.com', 'password' => 'password']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_accountant_can_login()
    {
        $user = User::factory()->create([ 'email' => 'acct@example.com', 'password' => Hash::make('password'), 'role' => 'accountant', 'is_active' => true, 'email_verified_at' => now() ]);

        $response = $this->post('/login', ['email' => 'acct@example.com', 'password' => 'password']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_employee_without_admin_dept_cannot_login()
    {
        $user = User::factory()->create([ 'email' => 'emp@example.com', 'password' => Hash::make('password'), 'role' => 'employee' ]);
        $employee = Employee::factory()->create([ 'user_id' => $user->id, 'email' => 'emp@example.com', 'mobile_number' => '1234567890' ]);
        // attach to non-administrative dept
        $employee->departments()->attach(2);

        $response = $this->post('/login', ['email' => 'emp@example.com', 'password' => 'password']);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([ 'email' => 'off@example.com', 'password' => Hash::make('password'), 'role' => 'accountant', 'is_active' => false, 'email_verified_at' => now() ]);
        $response = $this->post('/login', ['email' => 'off@example.com', 'password' => 'password']);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_employee_in_admin_dept_cannot_login()
    {
        $user = User::factory()->create([ 'email' => 'emp2@example.com', 'password' => Hash::make('password'), 'role' => 'employee' ]);
        $employee = Employee::factory()->create([ 'user_id' => $user->id, 'email' => 'emp2@example.com', 'mobile_number' => '1234567891' ]);
        $employee->departments()->attach(1);

        $response = $this->post('/login', ['email' => 'emp2@example.com', 'password' => 'password']);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_assign_role_when_creating_employee()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $data = [
            'employee_code' => 'EMP9999',
            'first_name' => 'Test',
            'last_name' => 'Hr',
            'email' => 'newhr@example.com',
            'mobile_number' => '9876543210',
            'gender' => 'male',
            'joining_date' => now()->format('Y-m-d'),
            'employee_type' => 'permanent',
            'designation' => 'Tester',
            'bank_name' => 'Test Bank',
            'account_number' => '123456',
            'account_holder_name' => 'Test Hr',
            'branch_name' => 'Main',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'hr',
        ];

        $response = $this->post('/employees', $data);
        $response->assertRedirect();

        $employee = Employee::where('email', 'newhr@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertEquals('hr', $employee->user->role);
        $this->assertTrue($employee->user->is_active);
        $this->assertNotNull($employee->user->email_verified_at);
    }

    public function test_admin_updating_role_activates_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $user = User::factory()->create([ 'role' => 'employee', 'is_active' => false ]);
        // create employee with all required details so update validation passes
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'mobile_number' => '1111111111',
            'date_of_birth' => '1990-01-01',
            'marital_status' => 'single',
            'blood_group' => 'A+',
            'citizenship_number' => 'CIT12345',
            'citizenship_issue_date' => '2010-01-01',
            'citizenship_issued_district' => config('nepal.districts')[0] ?? 'Kathmandu',
            'pan_number' => 'PAN123',
            'current_address' => 'Anywhere',
            'permanent_address' => 'Anywhere',
            'district' => config('nepal.districts')[0] ?? 'Kathmandu',
            'municipality' => 'Somewhere',
            'ward_number' => '1',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '2222222222',
            'emergency_contact_relation' => 'sister',
            'qualification' => 'Bachelors',
            'institution_name' => 'Uni',
            'experience_years' => 1,
            // other required fields for update
            'first_name' => 'John',
            'last_name' => 'Doe',
            'employee_code' => 'EMP1111',
            'gender' => 'male',
            'joining_date' => now()->format('Y-m-d'),
            'employee_type' => 'permanent',
            'designation' => 'Worker',
            'bank_name' => 'Bank',
            'account_number' => '123456',
            'account_holder_name' => 'John Doe',
            'branch_name' => 'Main',
            'employment_status' => 'active',
        ]);

        // give at least one department to satisfy validation
        $dept = Department::first() ?? Department::factory()->create();

        // use all existing employee attributes to satisfy validation rules
        $data = $employee->toArray();
        $data = array_merge($data, [
            'role' => 'hr',
            'employment_status' => 'active',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => false,
            'departments' => [$dept->id],
            'marital_status' => 'single',
        ]);

        // sanity check: payload should include role hr
        $this->assertArrayHasKey('role', $data);
        $this->assertEquals('hr', $data['role']);

        $response = $this->put("/employees/{$employee->id}", $data);

        // capture any error flash for debugging
        $session = $response->getSession();
        if ($session && $session->has('error')) {
            dump('SESSION ERROR:', $session->get('error'));
        }

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(); // route may vary, we only care that it redirected

        $user->refresh();
        $this->assertEquals('hr', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
    }
}
