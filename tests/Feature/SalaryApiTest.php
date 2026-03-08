<?php

use App\Models\Employee;
use App\Models\MonthlySalary;
use App\Models\User;

beforeEach(function () {
    // create user and employee relationship
    $this->user = User::factory()->create();
    $this->employee = Employee::factory()->create(['user_id' => $this->user->id]);
    $this->user->employee_id = $this->employee->id;
    $this->user->save();

    // default token to attach as header
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('Salary API', function () {

    test('monthly endpoint returns salary when record exists', function () {
        $salary = MonthlySalary::create([
            'employee_id' => $this->employee->id,
            'salary_month' => now()->format('Y-m'),
            'basic_salary' => 10000,
            'total_allowances' => 2000,
            'total_deductions' => 500,
            'gross_salary' => 11500,
            'net_salary' => 11000,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/salary/monthly?month=' . now()->month . '&year=' . now()->year);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Monthly salary retrieved successfully',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'salary_month',
                    'net_salary',
                    'payment_status',
                    // other fields omitted for brevity
                ],
            ]);
    });

    test('monthly endpoint returns 404 when salary missing', function () {
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/salary/monthly?month=1&year=1970');

        $response->assertStatus(404)
            ->assertJson(["message" => 'No salary record found for this month']);
    });

    test('status endpoint returns yearly overview', function () {
        // create multiple salary records spanning two months
        MonthlySalary::create([
            'employee_id' => $this->employee->id,
            'salary_month' => now()->format('Y-m'),
            'net_salary' => 5000,
            'payment_status' => 'paid',
        ]);
        MonthlySalary::create([
            'employee_id' => $this->employee->id,
            'salary_month' => now()->subMonth()->format('Y-m'),
            'net_salary' => 6000,
            'payment_status' => 'pending',
        ]);

        $year = now()->year;
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/salary/status?year={$year}");

        $response->assertStatus(200)
            ->assertJson([ 'year' => $year ])
            ->assertJsonStructure([
                'message',
                'year',
                'data' => [[]],
                'summary' => ['total_months', 'paid', 'pending', 'total_amount'],
            ]);
    });

    test('status endpoint 404 when no records', function () {
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/salary/status?year=1970');

        $response->assertStatus(404);
    });

    test('history endpoint returns paginated results', function () {
        // generating 15 records to trigger pagination
        for ($i = 1; $i <= 15; $i++) {
            MonthlySalary::create([
                'employee_id' => $this->employee->id,
                'salary_month' => now()->subMonths($i)->format('Y-m'),
                'net_salary' => 1000 + $i,
                'payment_status' => 'pending',
            ]);
        }

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/salary/history?year=' . now()->year);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['data', 'links', 'meta'],
                'summary' => ['total', 'paid', 'pending'],
                'pagination' => ['current_page', 'last_page'],
            ]);
    });

    test('history endpoint 404 when none found', function () {
        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/salary/history?year=1970');

        $response->assertStatus(404);
    });

    test('receipt endpoint returns receipt data and protects ownership', function () {
        $salary = MonthlySalary::create([
            'employee_id' => $this->employee->id,
            'salary_month' => now()->format('Y-m'),
            'net_salary' => 7500,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/salary/receipt/{$salary->id}");

        $response->assertStatus(200)
            ->assertJson([ 'message' => 'Salary receipt retrieved successfully' ])
            ->assertJsonStructure(['data' => ['employee', 'earnings', 'deductions', 'totals', 'attendance', 'payment']]);

        // create another employee/salary and attempt access
        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()->create(['user_id' => $otherUser->id]);
        $otherSalary = MonthlySalary::create([
            'employee_id' => $otherEmployee->id,
            'salary_month' => now()->format('Y-m'),
            'net_salary' => 5000,
        ]);

        $deny = $this->actingAs($this->user)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/salary/receipt/{$otherSalary->id}");

        $deny->assertStatus(403);
    });

});
