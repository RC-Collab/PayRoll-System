<?php

use App\Models\Employee;
use App\Models\User;
use App\Models\Qualification;
use App\Models\Experience;
use App\Models\EmergencyContact;

beforeEach(function () {
    // Create a test user with employee
    $this->user = User::factory()->create();
    $this->employee = Employee::factory()->create(['user_id' => $this->user->id]);
    $this->user->employee_id = $this->employee->id;
    $this->user->save();
});

describe('Employee Profile API', function () {
    
    test('authenticated user can get complete profile', function () {
        // give the employee a salary structure and some bank details so we can
        // verify those fields are included in the API response.
        $this->employee->salaryStructure()->create([
            'basic_salary' => 50000,
            'overtime_rate' => 250,
        ]);
        $this->employee->update([
            'bank_name' => 'Test Bank',
            'branch_name' => 'Main Branch',
            'account_number' => '1234567890',
            'account_holder_name' => 'Test Holder',
            'ifsc_code' => 'IFSC0001',
            'pan_number' => 'PAN1234',
            'uan_number' => 'UAN5678',
            'esi_number' => 'ESI9012',
        ]);

        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/employee/profile');
        
        // debugging: show body when failure occurs
        $response->dump();

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'personal' => [
                        'id', 'employee_code', 'first_name', 'middle_name', 'last_name',
                        'full_name', 'email', 'mobile_number', 'alternate_phone',
                        'date_of_birth', 'gender', 'marital_status', 'blood_group',
                        'nationality', 'religion', 'citizenship_number', 'citizenship_issue_date',
                        'citizenship_issued_district', 'pan_number', 'profile_image'
                    ],
                    'address' => [
                        'current_address', 'permanent_address',
                        'municipality', 'ward_number', 'district',
                        'city', 'state', 'country', 'postal_code'
                    ],
                    'emergency_contacts',
                    'qualifications',
                    'experiences',
                    'documents',
                    'employment' => [
                        'designation', 'department', 'employee_type',
                        'employment_status', 'joining_date',
                        'confirmation_date', 'probation_end_date', 'contract_end_date',
                        'work_shift', 'reporting_to',
                        'qualification', 'institution_name',
                        'experience_years'
                    ],
                    'salary'
                ]
            ])
            // verify salary and bank details values
            ->assertJsonPath('data.salary.basic_salary', 50000)
            ->assertJsonPath('data.salary.overtime_rate', 250)
            ->assertJsonPath('data.salary.bank_name', 'Test Bank')
            ->assertJsonPath('data.salary.account_number', '****7890');
    });

    test('unauthenticated user gets 401 error', function () {
        $response = $this->getJson('/api/employee/profile');
        
        $response->assertStatus(401);
    });

    test('user can update personal information', function () {
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/employee/profile/personal', [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'mobile_number' => '9876543210',
                'gender' => 'male',
                'religion' => 'Hindu'
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Personal information updated successfully'
            ]);
        
        $this->employee->refresh();
        $this->assertEquals('Updated', $this->employee->first_name);
        $this->assertEquals('Name', $this->employee->last_name);
        $this->assertEquals('Hindu', $this->employee->religion);
    });

    test('user can update address information', function () {
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/employee/profile/address', [
                'present_address' => '123 Main Street',
                'city' => 'Kathmandu',
                'state' => 'Bagmati',
                'country' => 'Nepal',
                'postal_code' => '44600'
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Address updated successfully'
            ]);
        
        $this->employee->refresh();
        $this->assertEquals('123 Main Street', $this->employee->present_address);
        $this->assertEquals('Kathmandu', $this->employee->city);
    });

    test('user can create qualification', function () {
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/employee/profile/qualifications', [
                'degree' => 'Bachelor',
                'institution' => 'University of Nepal',
                'board' => 'Tribhuvan',
                'year' => 2020,
                'percentage' => 75.5,
                'grade' => 'A',
                'specialization' => 'Computer Science',
                'start_date' => '2016-01-01',
                'end_date' => '2020-01-01',
                'is_pursuing' => false
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Qualification added successfully'
            ]);
        
        $this->assertDatabaseHas('qualifications', [
            'employee_id' => $this->employee->id,
            'degree' => 'Bachelor',
            'institution' => 'University of Nepal',
            'board' => 'Tribhuvan',
            'year' => 2020,
            'is_pursuing' => false,
        ]);
    });

    test('user can create experience', function () {
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/employee/profile/experiences', [
                'company' => 'Tech Company',
                'position' => 'Software Developer',
                'location' => 'Kathmandu',
                'start_date' => '2021-01-01',
                'end_date' => null,
                'is_current' => true,
                'description' => 'Working as a software developer',
                'achievements' => 'Built payroll module'
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Experience added successfully'
            ]);
        
        $this->assertDatabaseHas('experiences', [
            'employee_id' => $this->employee->id,
            'company' => 'Tech Company',
            'position' => 'Software Developer',
        ]);
    });

    test('user can create emergency contact', function () {
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/employee/profile/emergency-contacts', [
                'name' => 'John Doe',
                'relationship' => 'Brother',
                'phone' => '9876543210',
                'phone2' => '0123456789',
                'email' => 'john@example.com',
                'address' => '123 Street',
                'is_primary' => true
            ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Contact added successfully'
            ]);
        
        $this->assertDatabaseHas('emergency_contacts', [
            'employee_id' => $this->employee->id,
            'name' => 'John Doe',
            'is_primary' => true,
            'phone2' => '0123456789',
            'address' => '123 Street',
        ]);
    });

    test('user can get qualifications list', function () {
        // Create test qualifications
        Qualification::factory(3)->create(['employee_id' => $this->employee->id]);
        
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/employee/profile/qualifications');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonCount(3, 'data');
    });

    test('user can get experiences list', function () {
        // Create test experiences
        Experience::factory(2)->create(['employee_id' => $this->employee->id]);
        
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/employee/profile/experiences');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonCount(2, 'data');
    });

    test('user can get emergency contacts list', function () {
        // Create test contacts
        EmergencyContact::factory(2)->create(['employee_id' => $this->employee->id]);
        
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/employee/profile/emergency-contacts');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonCount(2, 'data');
    });

    test('user can delete qualification', function () {
        $qualification = Qualification::factory()->create(['employee_id' => $this->employee->id]);
        
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/employee/profile/qualifications/{$qualification->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Qualification deleted successfully'
            ]);
        
        $this->assertDatabaseMissing('qualifications', [
            'id' => $qualification->id,
        ]);
    });

    test('user can delete experience', function () {
        $experience = Experience::factory()->create(['employee_id' => $this->employee->id]);
        
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/employee/profile/experiences/{$experience->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Experience deleted successfully'
            ]);
        
        $this->assertDatabaseMissing('experiences', [
            'id' => $experience->id,
        ]);
    });

    test('user can delete emergency contact', function () {
        $contact = EmergencyContact::factory()->create(['employee_id' => $this->employee->id]);
        
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/employee/profile/emergency-contacts/{$contact->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Contact deleted successfully'
            ]);
        
        $this->assertDatabaseMissing('emergency_contacts', [
            'id' => $contact->id,
        ]);
    });

    test('user cannot access another employees profile', function () {
        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()->create(['user_id' => $otherUser->id]);
        $otherUser->employee_id = $otherEmployee->id;
        $otherUser->save();
        
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        // Try to delete other employee's qualification
        $qualification = Qualification::factory()->create(['employee_id' => $otherEmployee->id]);
        
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/employee/profile/qualifications/{$qualification->id}");
        
        // Should fail because it's not their own qualification
        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error'
            ]);
    });
});
