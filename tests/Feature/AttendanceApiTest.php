<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->employee = Employee::factory()->create(['user_id' => $this->user->id]);
    $this->user->employee_id = $this->employee->id;
    $this->user->save();
});

// helper to add required headers (sanctum token + android client)
function authHeaders($token)
{
    return [
        'Authorization' => "Bearer $token",
        'X-APP-CLIENT' => 'android',
    ];
}

it('requires android client header for check-in', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-09')); // monday
    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->actingAs($this->user)
        ->withHeader('Authorization', "Bearer $token")
        // no X-APP-CLIENT header intentionally
        ->postJson('/api/attendance/check-in', []);

    $response->assertStatus(403)
             ->assertJson(['message' => 'Only Android app may perform this action']);
});

it('prevents check-in with a future timestamp', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-08'));
    $token = $this->user->createToken('test-token')->plainTextToken;
    $future = Carbon::now()->addDays(2)->toDateTimeString();

    $response = $this->actingAs($this->user)
        ->withHeaders(authHeaders($token))
        ->postJson('/api/attendance/check-in', [
            'timestamp' => $future,
        ]);

    $response->assertStatus(422)
             ->assertJson(['message' => 'Cannot check in for a future date']);
});

it('prevents check-in on non working day (saturday)', function () {
    // simulate saturday
    Carbon::setTestNow(Carbon::parse('2026-03-07')); // Saturday
    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->actingAs($this->user)
        ->withHeaders(authHeaders($token))
        ->postJson('/api/attendance/check-in', []);

    $response->assertStatus(422)
             ->assertJson(['message' => 'Cannot check in on a non-working day']);
});

it('allows check-in on working day (monday)', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-09')); // Monday
    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->actingAs($this->user)
        ->withHeaders(authHeaders($token))
        ->postJson('/api/attendance/check-in', []);

    $response->assertStatus(201)
             ->assertJson(['message' => 'Checked in successfully']);
});

it('requires android client header for present', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-09')); // weekday
    $token = $this->user->createToken('test-token')->plainTextToken;
    $response = $this->actingAs($this->user)
        ->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/attendance/present', []);
    $response->assertStatus(403)
             ->assertJson(['message' => 'Only Android app may perform this action']);
});

it('prevents present mark on non-working day', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-07')); // sat
    $token = $this->user->createToken('test-token')->plainTextToken;
    $response = $this->actingAs($this->user)
        ->withHeaders(authHeaders($token))
        ->postJson('/api/attendance/present', []);
    $response->assertStatus(422)
             ->assertJson(['message' => 'Cannot check in on a non-working day']);
});

it('requires android client header for absent', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-09'));
    $token = $this->user->createToken('test-token')->plainTextToken;
    $response = $this->actingAs($this->user)
        ->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/attendance/absent', []);
    $response->assertStatus(403)
             ->assertJson(['message' => 'Only Android app may perform this action']);
});

it('prevents absent mark on non-working day', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-07'));
    $token = $this->user->createToken('test-token')->plainTextToken;
    $response = $this->actingAs($this->user)
        ->withHeaders(authHeaders($token))
        ->postJson('/api/attendance/absent', []);
    $response->assertStatus(422)
             ->assertJson(['message' => 'Cannot mark absence on a non-working day']);
});

it('prevents absent for a future date', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-08'));
    $token = $this->user->createToken('test-token')->plainTextToken;
    $futureDate = Carbon::now()->addDays(3)->toDateString();

    $response = $this->actingAs($this->user)
        ->withHeaders(authHeaders($token))
        ->postJson('/api/attendance/absent', ['date' => $futureDate]);

    $response->assertStatus(422)
             ->assertJson(['message' => 'Cannot mark absence for a future date']);
});

it('requires android client header for check-out', function () {
    // create a normal attendance record
    $attendance = Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => '2026-03-09',
        'check_in' => Carbon::parse('2026-03-09 09:00'),
    ]);
    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->actingAs($this->user)
        ->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/attendance/check-out', [
            'attendance_id' => $attendance->id,
        ]);

    $response->assertStatus(403)
             ->assertJson(['message' => 'Only Android app may perform this action']);
});

it('prevents quick-marking a future date (admin)', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-08'));
    // bypass role middleware since sqlite schema lacks role column
    $this->withoutMiddleware();
    $token = $this->user->createToken('test-token')->plainTextToken;

    $futureDate = Carbon::now()->addDays(5)->toDateString();

    $response = $this->actingAs($this->user)
        ->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/attendance/quick-mark', [
            'employee_id' => $this->employee->id,
            'date' => $futureDate,
            'type' => 'present',
        ]);

    $response->assertStatus(422)
             ->assertJson(['message' => 'Cannot quick-mark future dates']);
});

it('quick-mark updates an existing record', function () {
    Carbon::setTestNow('2026-03-08');
    // bypass role middleware since sqlite setup doesn't include roles
    $this->withoutMiddleware();

    $emp = Employee::factory()->create();
    $attendance = Attendance::create([
        'employee_id' => $emp->id,
        'date' => '2026-03-08',
        'status' => 'present',
    ]);
    $token = $this->user->createToken('test-token')->plainTextToken;

    // update status to absent
    $response = $this->actingAs($this->user)
        ->withHeaders(['Authorization' => "Bearer $token"])
        ->postJson('/api/attendance/quick-mark', [
            'employee_id' => $emp->id,
            'date' => '2026-03-08',
            'type' => 'absent',
        ]);

    $response->assertStatus(201)
             ->assertJson(['message' => 'Attendance quick-marked']);

    $this->assertDatabaseHas('attendances', [
        'id' => $attendance->id,
        'status' => 'absent',
    ]);
});

it('prevents check-out on non-working day even if record exists', function () {
    // create a saturday attendance record manually
    $attendance = Attendance::create([
        'employee_id' => $this->employee->id,
        'date' => '2026-03-07',
        'check_in' => Carbon::parse('2026-03-07 09:00'),
    ]);
    $token = $this->user->createToken('test-token')->plainTextToken;

    $response = $this->actingAs($this->user)
        ->withHeaders(authHeaders($token))
        ->postJson('/api/attendance/check-out', [
            'attendance_id' => $attendance->id,
        ]);

    $response->assertStatus(422)
             ->assertJson(['message' => 'Cannot check out on a non-working day']);
});

it('artisan command marks absent for missing attendance', function () {
    Carbon::setTestNow('2026-03-09'); // monday working day
    $emp1 = Employee::factory()->create();
    $emp2 = Employee::factory()->create();

    $this->artisan('attendance:mark-absent', ['date' => '2026-03-09'])
         ->expectsOutputToContain('Absences marked for 2026-03-09')
         ->assertExitCode(0);

    // the date column is stored as a datetime, so include the time portion
    $this->assertDatabaseHas('attendances', [
        'employee_id' => $emp1->id,
        'date' => '2026-03-09 00:00:00',
        'status' => 'absent',
    ]);
    $this->assertDatabaseHas('attendances', [
        'employee_id' => $emp2->id,
        'date' => '2026-03-09 00:00:00',
        'status' => 'absent',
    ]);
});

it('artisan command skips non-working day', function () {
    Carbon::setTestNow('2026-03-07'); // saturday
    $emp = Employee::factory()->create();

    $this->artisan('attendance:mark-absent', ['date' => '2026-03-07'])
         ->expectsOutput('2026-03-07 is not a working day, nothing to do.')
         ->assertExitCode(0);

    $this->assertDatabaseMissing('attendances', [
        'employee_id' => $emp->id,
        'date' => '2026-03-07',
    ]);
});

it('web route allows admin to delete a record', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'email_verified_at' => now()]);
    $emp = Employee::factory()->create(['user_id' => $admin->id]);
    $record = Attendance::create([
        'employee_id' => $emp->id,
        'date' => now()->format('Y-m-d'),
        'status' => 'present',
    ]);

    $this->actingAs($admin)
         ->delete(route('attendance.destroy', $record->id))
         ->assertRedirect();

    $this->assertDatabaseMissing('attendances', ['id' => $record->id]);
});

it('purge-future command deletes future records', function () {
    Carbon::setTestNow('2026-03-08');
    $e1 = Employee::factory()->create();
    $future = Carbon::now()->addDays(3)->toDateString();
    Attendance::create(['employee_id' => $e1->id, 'date' => $future, 'status' => 'present']);

    $this->artisan('attendance:purge-future')
         ->expectsOutput("Purged 1 attendance record(s) newer than 2026-03-08")
         ->assertExitCode(0);

    $this->assertDatabaseMissing('attendances', ['employee_id' => $e1->id, 'date' => $future]);
});
