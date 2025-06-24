<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Register a new user
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'role' => 'sometimes|in:admin,manager,employee'
            ], [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.email' => 'Please provide a valid email address',
                'email.unique' => 'Email already exists',
                'password.required' => 'Password is required',
                'password.confirmed' => 'Password confirmation does not match',
                'role.in' => 'Role must be admin, manager, or employee'
            ]);

            // Default role adalah employee
            $role = $validated['role'] ?? 'employee';

            // Hanya admin yang bisa create admin/manager
            if (($role === 'admin' || $role === 'manager') && auth()->check()) {
                if (!auth()->user()->isAdmin()) {
                    return $this->forbiddenResponse('Only admin can create admin or manager accounts');
                }
            }

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'email_verified_at' => now() // Auto verify untuk simplicity
            ]);

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->createdResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'User registered successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', 500, $e->getMessage());
        }
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ], [
                'email.required' => 'Email is required',
                'email.email' => 'Please provide a valid email address',
                'password.required' => 'Password is required'
            ]);

            // Attempt login
            if (!Auth::attempt($validated)) {
                return $this->unauthorizedResponse('Invalid credentials');
            }

            $user = Auth::user();

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Login successful');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed', 500, $e->getMessage());
        }
    }

    /**
     * Get authenticated user profile
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ], 'User profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve user profile', 500, $e->getMessage());
        }
    }

    /**
     * Update user profile
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validasi input
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'current_password' => 'sometimes|required_with:password|string',
                'password' => ['sometimes', 'confirmed', Password::min(8)->letters()->numbers()]
            ], [
                'name.max' => 'Name cannot exceed 255 characters',
                'email.email' => 'Please provide a valid email address',
                'email.unique' => 'Email already exists',
                'current_password.required_with' => 'Current password is required when changing password',
                'password.confirmed' => 'Password confirmation does not match'
            ]);

            // Jika update password, validasi current password
            if (isset($validated['password'])) {
                if (
                    !isset($validated['current_password']) ||
                    !Hash::check($validated['current_password'], $user->password)
                ) {
                    return $this->errorResponse('Current password is incorrect', 422);
                }
            }

            // Update user data
            $updateData = [];
            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }
            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            return $this->updatedResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'updated_at' => $user->updated_at
            ], 'Profile updated successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update profile', 500, $e->getMessage());
        }
    }

    /**
     * Logout user (revoke current token)
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed', 500, $e->getMessage());
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     * POST /api/auth/logout-all
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            // Revoke all tokens
            $request->user()->tokens()->delete();

            return $this->successResponse(null, 'Logged out from all devices successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to logout from all devices', 500, $e->getMessage());
        }
    }

    /**
     * Refresh token (optional - create new token)
     * POST /api/auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to refresh token', 500, $e->getMessage());
        }
    }
}
