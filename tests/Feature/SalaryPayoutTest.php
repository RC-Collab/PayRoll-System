<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\MonthlySalary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryPayoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function accountant_can_view_payout_form()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        $employee = Employee::factory()->create();

        $salary = MonthlySalary::create([
            'employee_id' => $employee->id,
            'salary_month' => now()->subMonth()->format('Y-m'),
            'basic_salary' => 10000,
            'total_allowances' => 1000,
            'total_deductions' => 500,
            'gross_salary' => 11000,
            'net_salary' => 10500,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('salary.payout.form', $salary->id))
            ->assertStatus(200)
            ->assertSee('Salary Payout')
            ->assertSee($employee->full_name);
    }

    /** @test */
    public function posting_payout_marks_salary_paid_and_saves_details()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        $employee = Employee::factory()->create();

        $salary = MonthlySalary::create([
            'employee_id' => $employee->id,
            'salary_month' => now()->subMonth()->format('Y-m'),
            'basic_salary' => 5000,
            'total_allowances' => 500,
            'total_deductions' => 200,
            'gross_salary' => 5500,
            'net_salary' => 5300,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->post(route('salary.payout.process', $salary->id), [
                'payment_method' => 'Cheque',
                'payment_bank' => 'Test Bank',
                'cheque_number' => 'CHQ12345',
                'paid_amount' => 5300,
                'payment_date' => now()->format('Y-m-d'),
            ]);

        $response->assertRedirect(route('salary.history'));
        $response->assertSessionHas('success');

        $salary->refresh();
        $this->assertEquals('paid', $salary->payment_status);
        $this->assertEquals('Test Bank', $salary->payment_bank);
        $this->assertEquals('CHQ12345', $salary->cheque_number);
        $this->assertEquals(5300, $salary->paid_amount);
        $this->assertNotNull($salary->paid_at);
        if (Schema::hasColumn('monthly_salaries', 'paid_by')) {
            $this->assertEquals($user->id, $salary->paid_by);
        }

        // ensure history page shows payment info
        $this->actingAs($user)
            ->get(route('salary.history'))
            ->assertSee('Test Bank')
            ->assertSee('CHQ12345');

        // payslip page should display full breakdown
        $this->actingAs($user)
            ->get(route('salary.payslip', $salary->id))
            ->assertSee('Salary Payslip')
            ->assertSee('Net Salary')
            ->assertSee('रु ' . number_format($salary->net_salary, 2));
    }

    /** @test */
    public function index_shows_every_employee_but_blocks_open_month_actions()
    {
        $user = User::factory()->create(['role' => 'accountant']);

        // create three employees to represent uncalculated, pending and paid states
        $uncalc = Employee::factory()->create([
            'first_name' => 'Uncalc',
            'last_name' => 'Emp',
        ]);
        $pending = Employee::factory()->create([
            'first_name' => 'Pending',
            'last_name' => 'Emp',
        ]);
        MonthlySalary::create([
            'employee_id' => $pending->id,
            'salary_month' => now()->format('Y-m'),
            'basic_salary' => 8000,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'gross_salary' => 8000,
            'net_salary' => 8000,
            'payment_status' => 'pending',
        ]);
        $paid = Employee::factory()->create([
            'first_name' => 'Paid',
            'last_name' => 'Emp',
        ]);
        MonthlySalary::create([
            'employee_id' => $paid->id,
            'salary_month' => now()->format('Y-m'),
            'basic_salary' => 9000,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'gross_salary' => 9000,
            'net_salary' => 9000,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($user)->get(route('salary.index'));
        $response->assertStatus(200);
        // all three entries should be visible regardless of status
        $response->assertSee('Uncalc Emp');
        $response->assertSee('Pending Emp');
        $response->assertSee('Paid Emp');

        // month is current, so actions should be disabled/omitted
        $response->assertSee('Month still open');
        $response->assertSee('disabled');
        $response->assertDontSee('form method="POST"');
        $response->assertDontSee('Payout');
    }

    /** @test */
    public function past_month_allows_calculation_actions()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        $employee = Employee::factory()->create(['first_name' => 'Past', 'last_name' => 'Emp']);

        // no salary exists yet for previous month
        $lastMonth = now()->subMonth()->format('Y-m');
        $response = $this->actingAs($user)
            ->get(route('salary.index', ['month' => $lastMonth]));
        $response->assertStatus(200);
        $response->assertDontSee('Month still open');
        // modal calculate button not disabled
        $response->assertDontSee('disabled');
        $response->assertSee('form method="POST"', false);
        $response->assertSee('name="month" value="' . $lastMonth . '"', false);
        $response->assertSee('name="employee_id" value="' . $employee->id . '"', false);
    }

    /** @test */
    public function cannot_calculate_or_payout_for_open_month()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        $emp = Employee::factory()->create();
        $month = now()->format('Y-m');

        // try single employee calculation by POST
        $response = $this->actingAs($user)
            ->post(route('salary.calculate'), [
                'month' => $month,
                'calculate_for' => 'employee',
                'employee_id' => $emp->id,
            ]);
        $response->assertRedirect(route('salary.index', ['month' => $month]));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('monthly_salaries', [
            'employee_id' => $emp->id,
            'salary_month' => $month,
        ]);

        // create a salary record for current month and try to view payout form
        $salary = MonthlySalary::create([
            'employee_id' => $emp->id,
            'salary_month' => $month,
            'basic_salary' => 1000,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'gross_salary' => 1000,
            'net_salary' => 1000,
            'payment_status' => 'pending',
        ]);

        $resp2 = $this->actingAs($user)->get(route('salary.payout.form', $salary->id));
        $resp2->assertRedirect(route('salary.index', ['month' => $month]));
        $resp2->assertSessionHas('error');
    }

    /** @test */
    public function absent_days_are_deducted_when_calculating_salary()
    {
        $user = User::factory()->create(['role' => 'accountant']);
        $employee = Employee::factory()->create();

        // attach a simple salary structure so calculation will run
        $employee->salaryStructure()->create([
            'basic_salary' => 3000,
            'dearness_allowance' => 0,
            'house_rent_allowance' => 0,
            'medical_allowance' => 0,
            'tiffin_allowance' => 0,
            'transport_allowance' => 0,
            'special_allowance' => 0,
            'overtime_rate' => 0,
        ]);

        // create attendance for every day of the month: first day absent, rest present
        $start = now()->startOfMonth();
        $daysInMonth = $start->daysInMonth;
        for ($i = 0; $i < $daysInMonth; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            \App\Models\Attendance::create([
                'employee_id' => $employee->id,
                'date' => $date,
                'status' => $i === 0 ? 'absent' : 'present',
            ]);
        }

        $this->actingAs($user)
            ->post(route('salary.calculate'), [
                'month' => now()->subMonth()->format('Y-m'),
                'calculate_for' => 'employee',
                'employee_id' => $employee->id,
            ]);

        $salary = MonthlySalary::where('employee_id', $employee->id)
            ->where('salary_month', now()->subMonth()->format('Y-m'))
            ->first();

        $this->assertNotNull($salary);

        $perDay = 3000 / 30;
        $this->assertEquals($perDay, $salary->absent_deduction_amount);
        $this->assertEquals(3000 - $perDay, $salary->net_salary);
    }
}

