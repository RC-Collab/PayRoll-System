<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Models\ActivationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_activate_and_login()
    {
        // disable real email sending
        Mail::fake();

        // create employee without a user
        $employee = Employee::factory()->create([
            'email' => 'newuser@example.com',
            'mobile_number' => '9998887777',
            'is_active' => false,
            'employment_status' => 'inactive',
        ]);

        // send activation code
        $response = $this->post('/activate/send-code', [
            'email' => 'newuser@example.com',
            'phone' => '9998887777',
        ]);

        $response->assertRedirect(route('activate.verify.form'));
        $this->assertNotNull(session('activation_email'));

        // fetch the code from DB
        $code = ActivationCode::first()->code;

        // verify and set password
        $response = $this->post('/activate/verify', [
            'code' => $code,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // ensure user created and employee updated
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertTrue($user->is_active);

        $employee->refresh();
        $this->assertEquals('active', $employee->employment_status);

        // login with new credentials
        $login = $this->post('/login', ['email' => 'newuser@example.com', 'password' => 'secret123']);
        $login->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }
}
