<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // create an admin user for routes guarded by role middleware
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
});

it('allows admin to view deductions index', function () {
    $response = $this->actingAs($this->admin)
        ->get('/salary/deductions');

    $response->assertStatus(200);
    $response->assertSee('Deduction Management');
});

it('redirects non-admin from deductions index', function () {
    $user = User::factory()->create(['role' => 'employee', 'is_active' => true, 'email_verified_at' => now()]);
    $response = $this->actingAs($user)->get('/salary/deductions');
    $response->assertStatus(403); // role middleware should deny
});

it('allows admin to create, update and delete a deduction', function () {
    $this->actingAs($this->admin);

    // create
    $response = $this->post('/salary/deductions', [
        'name' => 'Test Deduction',
        'code' => 'TEST',
        'type' => 'fixed',
        'default_value' => 100,
        'is_mandatory' => 1,
        'is_active' => 1,
        'sort_order' => 5,
    ]);

    $response->assertRedirect('/salary/deductions');
    $this->assertDatabaseHas('deductions', ['code' => 'TEST', 'name' => 'Test Deduction']);

    $deduction = \App\Models\Deduction::where('code','TEST')->first();
    // update
    $response = $this->put("/salary/deductions/{$deduction->id}", [
        'name' => 'Changed Name',
        'code' => 'TEST',
        'type' => 'fixed',
        'default_value' => 200,
        'is_mandatory' => 0,
        'is_active' => 1,
        'sort_order' => 3,
    ]);
    $response->assertRedirect('/salary/deductions');
    $this->assertDatabaseHas('deductions', ['id' => $deduction->id, 'name' => 'Changed Name', 'default_value' => 200]);

    // delete
    $response = $this->delete("/salary/deductions/{$deduction->id}");
    $response->assertRedirect('/salary/deductions');
    $this->assertDatabaseMissing('deductions', ['id' => $deduction->id]);
});

