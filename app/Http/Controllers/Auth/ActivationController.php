<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\ActivationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ActivationController extends Controller
{
    /**
     * Step 1: Show activation form
     */
    public function showForm()
    {
        return view('auth.activate');
    }

    /**
     * Step 2: Send activation code
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        // normalize inputs to avoid mismatches (trim, lower-case email, digits-only phone)
        $email = strtolower(trim($request->email));
        $phone = preg_replace('/\D+/', '', $request->phone);

        // Try to find a matching User first
        $user = User::where('email', $email)
                    ->where('phone', $phone)
                    ->first();

        // If no User found, try to find an Employee with matching email & mobile
        if (!$user) {
            $employee = Employee::whereRaw('LOWER(email) = ?', [$email])
                                ->where('mobile_number', $phone)
                                ->first();

            if ($employee) {
                // If an Employee exists but has no linked User, create one
                if ($employee->user_id) {
                    $user = User::find($employee->user_id);
                } else {
                    $user = User::create([
                        'name' => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
                        'email' => strtolower(trim($employee->email)),
                        'phone' => $phone,
                        'role' => 'employee',
                        // supply temporary password (not used) so DB allows insert
                        'password' => Hash::make(Str::random(16)),
                        'is_active' => false,
                    ]);

                    // Link employee -> user
                    $employee->user_id = $user->id;
                    $employee->save();
                }
            }
        }

        if (!$user) {
            // provide a clearer message depending on which pieces are wrong
            $emailExists = Employee::whereRaw('LOWER(email) = ?', [$email])->exists() || User::where('email', $email)->exists();
            $phoneExists = Employee::where('mobile_number', $phone)->exists() || User::where('phone', $phone)->exists();

            if ($emailExists || $phoneExists) {
                return back()->withErrors([
                    'email' => 'Email and phone number do not match our records. Please contact HR.'
                ])->withInput();
            }

            return back()->withErrors([
                'email' => 'Email and phone number are not registered. Please contact HR.'
            ])->withInput();
        }

        // Check if already active
        if ($user->is_active) {
            // If user is already active, they can simply login or may already be authenticated
            return redirect()->route('login')->with('status', 'Account already activated. If you recently verified, you should be able to use your credentials to login.');
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any old unused codes for this user
        ActivationCode::where('email', $email)
                 ->where('phone', $phone)
                 ->where('is_used', false)
                 ->delete();

        // Save new code
        ActivationCode::create([
            'email' => $email,
            'phone' => $phone,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_used' => false
        ]);

        // Send email
        try {
            Mail::send('emails.activation', [
                'code' => $code, 
                'user' => $user,
                'expiry' => Carbon::now()->addMinutes(15)->format('h:i A')
            ], function($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('🔐 Account Activation Code - Payroll System');
            });

            // Store normalized email/phone in session for the next step
            session(['activation_email' => $email]);
            session(['activation_phone' => $phone]);

            return redirect()->route('activate.verify.form')
                ->with('status', 'Activation code sent to your email!');

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Failed to send email. Please try again. Error: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Step 3: Show verify code form
     */
    public function showVerifyForm()
    {
        if (!session('activation_email')) {
            return redirect()->route('activate.form');
        }
        return view('auth.verify-code');
    }

    /**
     * Step 4: Verify code and activate account
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $email = session('activation_email');
        $phone = session('activation_phone');

        // normalize stored values in case they were saved with different formatting
        $email = strtolower(trim($email));
        $phone = preg_replace('/\D+/', '', $phone);

        if (!$email || !$phone) {
            return redirect()->route('activate.form')
                ->withErrors(['email' => 'Session expired. Please start over.']);
        }

        // Find valid code
        $activationCode = ActivationCode::where('email', $email)
                                        ->where('phone', $phone)
                                        ->where('code', $request->code)
                                        ->where('is_used', false)
                                        ->where('expires_at', '>', Carbon::now())
                                        ->latest()
                                        ->first();

        if (!$activationCode) {
            return back()->withErrors([
                'code' => 'Invalid or expired code. Please request a new one.'
            ])->withInput();
        }

        // Find user (use normalized email)
        $user = User::where('email', $email)
                ->where('phone', $phone)
                ->first();

        if (!$user) {
            return redirect()->route('activate.form')
                ->withErrors(['email' => 'Subscriber not found. Your email/phone combo may be incorrect – please contact HR.']);
        }

        // Update user
        $user->password = Hash::make($request->password);
        $user->is_active = true;
        $user->activated_at = Carbon::now();
        $user->save();

        // immediately authenticate so they can continue
        Auth::login($user);

        // Update associated employee's employment_status to active
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $employee->employment_status = 'active';
            $employee->save();
        }

        // Mark code as used
        $activationCode->is_used = true;
        $activationCode->save();

        // Clear session
        session()->forget(['activation_email', 'activation_phone']);

        // After logging in above, take them to home/dashboard directly
        return redirect()->intended('/')
            ->with('status', '✅ Account activated and logged in! Welcome aboard.');
    }

    /**
     * Step 5: Resend code
     */
    public function resendCode(Request $request)
    {
        $email = session('activation_email');
        $phone = session('activation_phone');

        if (!$email || !$phone) {
            return redirect()->route('activate.form')
                ->withErrors(['email' => 'Session expired. Please start over.']);
        }

        $user = User::where('email', $email)
                    ->where('phone', $phone)
                    ->first();

        if (!$user) {
            return redirect()->route('activate.form')
                ->withErrors(['email' => 'Subscriber not found. Please re-enter correct email/phone or contact HR.']);
        }

        // Generate new code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old unused codes
        ActivationCode::where('email', $email)
                     ->where('phone', $phone)
                     ->where('is_used', false)
                     ->delete();

        // Save new code
        ActivationCode::create([
            'email' => $email,
            'phone' => $phone,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_used' => false
        ]);

        // Send email
        try {
            Mail::send('emails.activation', [
                'code' => $code, 
                'user' => $user,
                'expiry' => Carbon::now()->addMinutes(15)->format('h:i A')
            ], function($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('🔐 New Activation Code - Payroll System');
            });

            return back()->with('status', 'New activation code sent to your email!');

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Failed to send email. Please try again.'
            ]);
        }
    }

    /**
     * API Version for Mobile App
     */
    public function apiSendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        // normalize inputs
        $email = strtolower(trim($request->email));
        $phone = preg_replace('/\D+/', '', $request->phone);

        $user = User::where('email', $email)
                    ->where('phone', $phone)
                    ->first();

        // If no User found, try to find an Employee and create a User if needed
        if (!$user) {
            $employee = Employee::whereRaw('LOWER(email) = ?', [$email])
                                ->where('mobile_number', $phone)
                                ->first();

            if ($employee) {
                if ($employee->user_id) {
                    $user = User::find($employee->user_id);
                } else {
                    $user = User::create([
                        'name' => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
                        'email' => strtolower(trim($employee->email)),
                        'phone' => $phone,
                        'role' => 'employee',
                        'password' => Hash::make(Str::random(16)),
                        'is_active' => false,
                    ]);

                    $employee->user_id = $user->id;
                    $employee->save();
                }
            }
        }

        if (!$user) {
            return response()->json([
                'message' => 'No account found with these credentials'
            ], 404);
        }

        if ($user->is_active) {
            return response()->json([
                'message' => 'Account already activated. Please login with your credentials.'
            ], 400);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old unused codes
        ActivationCode::where('email', $email)
                 ->where('phone', $phone)
                 ->where('is_used', false)
                 ->delete();

        // Save new code
        ActivationCode::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_used' => false
        ]);

        try {
            Mail::send('emails.activation', [
                'code' => $code, 
                'user' => $user,
                'expiry' => Carbon::now()->addMinutes(15)->format('h:i A')
            ], function($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('🔐 Account Activation Code - Payroll System');
            });

            return response()->json([
                'message' => 'Activation code sent to your email',
                'email' => $user->email
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Verify Code and Activate
     */
    public function apiVerifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed'
        ]);

        // normalize inputs
        $email = strtolower(trim($request->email));
        $phone = preg_replace('/\D+/', '', $request->phone);

        // Find valid code
        $activationCode = ActivationCode::where('email', $email)
                        ->where('phone', $phone)
                        ->where('code', $request->code)
                        ->where('is_used', false)
                        ->where('expires_at', '>', Carbon::now())
                        ->latest()
                        ->first();

        if (!$activationCode) {
            return response()->json([
                'message' => 'Invalid or expired code'
            ], 400);
        }

        // Find user
        $user = User::where('email', $email)
                ->where('phone', $phone)
                ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Update user
        $user->password = Hash::make($request->password);
        $user->is_active = true;
        $user->activated_at = Carbon::now();
        $user->save();

        // also log them into the session (web only) so web users see immediate access
        try {
            Auth::login($user);
        } catch (\Exception $e) {
            // ignore session login failures in API contexts
        }

        // Update associated employee's employment_status to active
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $employee->employment_status = 'active';
            $employee->save();
        }

        // Mark code as used
        $activationCode->is_used = true;
        $activationCode->save();

        // Revoke prior tokens then generate a fresh API token for mobile
        try {
            $user->tokens()->delete();
        } catch (\Exception $e) {
            // ignore
        }

        // Generate token for auto-login
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Account activated successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ], 200);
    }

    /**
     * API Resend Code
     */
    public function apiResendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string'
        ]);

        $user = User::where('email', $request->email)
                    ->where('phone', $request->phone)
                    ->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Generate new code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old unused codes
        ActivationCode::where('email', $request->email)
                     ->where('phone', $request->phone)
                     ->where('is_used', false)
                     ->delete();

        // Save new code
        ActivationCode::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_used' => false
        ]);

        try {
            Mail::send('emails.activation', [
                'code' => $code, 
                'user' => $user,
                'expiry' => Carbon::now()->addMinutes(15)->format('h:i A')
            ], function($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('🔐 New Activation Code - Payroll System');
            });

            return response()->json([
                'message' => 'New code sent to your email'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * API Send Reset Code (Forgot Password)
 */
public function apiSendResetCode(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'message' => 'Email not found in our system'
        ], 404);
    }

    // Generate 6-digit code
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Delete old unused codes
    ActivationCode::where('email', $request->email)
                 ->where('is_used', false)
                 ->delete();

    // Save new code
    ActivationCode::create([
        'email' => $request->email,
        'phone' => $user->phone, // Store phone too
        'code' => $code,
        'expires_at' => Carbon::now()->addMinutes(15),
        'is_used' => false
    ]);

    try {
        Mail::send('emails.activation', [
            'code' => $code, 
            'user' => $user,
            'expiry' => Carbon::now()->addMinutes(15)->format('h:i A')
        ], function($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('🔐 Password Reset Code - Payroll System');
        });

        return response()->json([
            'message' => 'Reset code sent to your email'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to send email',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * API Reset Password
 */
public function apiResetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string|size:6',
        'password' => 'required|string|min:6|confirmed'
    ]);

    // Find valid code
    $activationCode = ActivationCode::where('email', $request->email)
                                    ->where('code', $request->code)
                                    ->where('is_used', false)
                                    ->where('expires_at', '>', Carbon::now())
                                    ->latest()
                                    ->first();

    if (!$activationCode) {
        return response()->json([
            'message' => 'Invalid or expired code'
        ], 400);
    }

    // Find user
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    // Update password
    $user->password = Hash::make($request->password);
    $user->save();

    // Mark code as used
    $activationCode->is_used = true;
    $activationCode->save();

    return response()->json([
        'message' => 'Password reset successfully'
    ], 200);
}
}