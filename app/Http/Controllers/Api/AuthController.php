<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Login with email and password
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $email = strtolower(trim($validated['email']));

            $user = User::where('email', $email)->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Ensure account has been activated
            if (! $user->is_active) {
                return response()->json([
                    'message' => 'Account not activated',
                ], 403);
            }

            // Ensure linked employee (if exists) is active in employment_status
            if ($user->employee && $user->employee->employment_status !== 'active') {
                return response()->json([
                    'message' => 'Employee profile not active',
                ], 403);
            }

            // Revoke previous tokens to avoid stale token issues, then issue a fresh one
            try {
                $user->tokens()->delete();
            } catch (\Exception $e) {
                // ignore token deletion failures
            }

            $token = $user->createToken('android-app')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/logout
     * Logout user - revoke current token
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout successful',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/profile
     * Get current user profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Profile retrieved successfully',
                'data' => new UserResource($user),
                'employee' => [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'last_name' => $employee->last_name,
                    'email' => $employee->email,
                    'mobile_number' => $employee->mobile_number,
                    'designation' => $employee->designation,
                    'employee_type' => $employee->employee_type,
                    'employment_status' => $employee->employment_status,
                    'profile_image' => $employee->profile_image,
                    'joining_date' => $employee->joining_date?->format('Y-m-d'),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/profile/update
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'mobile_number' => 'nullable|string|max:20',
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            $user = $request->user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee profile not found',
                ], 404);
            }

            // Update employee profile
            if (isset($validated['first_name']) || isset($validated['middle_name']) || isset($validated['last_name']) || isset($validated['mobile_number'])) {
                $employee->update([
                    'first_name' => $validated['first_name'] ?? $employee->first_name,
                    'middle_name' => $validated['middle_name'] ?? $employee->middle_name,
                    'last_name' => $validated['last_name'] ?? $employee->last_name,
                    'mobile_number' => $validated['mobile_number'] ?? $employee->mobile_number,
                ]);
            }

            // Update password if provided
            if (isset($validated['new_password'])) {
                if (!isset($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                    return response()->json([
                        'message' => 'Current password is incorrect',
                    ], 422);
                }

                $user->update([
                    'password' => Hash::make($validated['new_password']),
                ]);
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => new UserResource($user),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}