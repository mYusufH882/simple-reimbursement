<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\Models\User;
use App\Models\Reimbursement;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    use ApiResponseTrait;
    /**
     * Get admin dashboard statistics
     * GET /api/admin/dashboard
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_reimbursements' => Reimbursement::count(),
                'pending_reimbursements' => Reimbursement::where('status', 'pending')->count(),
                'approved_reimbursements' => Reimbursement::where('status', 'approved')->count(),
                'rejected_reimbursements' => Reimbursement::where('status', 'rejected')->count(),
                'total_categories' => Category::count(),
                'monthly_approved_amount' => Reimbursement::where('status', 'approved')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
                'users_by_role' => User::selectRaw('role, COUNT(*) as count')
                    ->groupBy('role')
                    ->pluck('count', 'role')
                    ->toArray(),
                'monthly_stats' => [
                    'current_month' => now()->format('Y-m'),
                    'submissions_this_month' => Reimbursement::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
                    'approvals_this_month' => Reimbursement::where('status', 'approved')
                        ->whereMonth('approved_at', now()->month)
                        ->whereYear('approved_at', now()->year)
                        ->count()
                ]
            ];

            return $this->successResponse($stats, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve dashboard statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get all users list
     * GET /api/admin/users
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $query = User::select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at');

            // Apply filters
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sort by latest first
            $query->where('role', '!=', 'admin')
                ->orderBy('created_at', 'desc');

            // Pagination
            $users = $query->paginate($request->input('per_page', 15));

            return $this->successResponse($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve users', 500, $e->getMessage());
        }
    }

    /**
     * Get specific user detail
     * GET /api/admin/users/{id}
     */
    public function getUser(string $id): JsonResponse
    {
        try {
            $user = User::select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at', 'updated_at')
                ->whereIn('role', [User::ROLE_EMPLOYEE, User::ROLE_MANAGER])
                ->with(['reimbursements' => function ($query) {
                    $query->select('id', 'user_id', 'title', 'amount', 'status', 'created_at')
                        ->latest()
                        ->limit(5);
                }])
                ->findOrFail($id);

            // Add user statistics
            $userStats = [
                'total_reimbursements' => $user->reimbursements()->count(),
                'pending_reimbursements' => $user->reimbursements()->where('status', 'pending')->count(),
                'approved_reimbursements' => $user->reimbursements()->where('status', 'approved')->count(),
                'rejected_reimbursements' => $user->reimbursements()->where('status', 'rejected')->count(),
                'total_approved_amount' => $user->reimbursements()->where('status', 'approved')->sum('amount'),
                'last_activity' => $user->reimbursements()->latest()->first()?->created_at
            ];

            $user->statistics = $userStats;

            return $this->successResponse($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('User not found');
        }
    }

    /**
     * Create new user
     * POST /api/admin/users
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'role' => 'required|in:admin,manager,employee'
            ], [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.email' => 'Please provide a valid email address',
                'email.unique' => 'Email already exists',
                'password.required' => 'Password is required',
                'password.confirmed' => 'Password confirmation does not match',
                'role.required' => 'Role is required',
                'role.in' => 'Role must be admin, manager, or employee'
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'email_verified_at' => now()
            ]);

            return $this->createdResponse(
                $user->only(['id', 'name', 'email', 'role', 'created_at']),
                'User created successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create user', 500, $e->getMessage());
        }
    }

    /**
     * Update user role
     * PUT /api/admin/users/{id}/role
     */
    public function updateUserRole(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role' => 'required|in:admin,manager,employee'
            ], [
                'role.required' => 'Role is required',
                'role.in' => 'Role must be admin, manager, or employee'
            ]);

            $user = User::findOrFail($id);

            // Prevent changing own role
            if ($user->id === auth()->id()) {
                return $this->errorResponse('Cannot change your own role', 422);
            }

            $oldRole = $user->role;
            $user->update(['role' => $validated['role']]);

            return $this->updatedResponse(
                $user->only(['id', 'name', 'email', 'role', 'updated_at']),
                "User role updated from {$oldRole} to {$validated['role']}"
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update user role', 500, $e->getMessage());
        }
    }

    /**
     * Update user profile (admin can update any user)
     * PUT /api/admin/users/{id}
     */
    public function updateUser(Request $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
                'role' => 'sometimes|in:admin,manager,employee',
                'password' => ['sometimes', 'confirmed', Password::min(8)->letters()->numbers()]
            ], [
                'name.max' => 'Name cannot exceed 255 characters',
                'email.email' => 'Please provide a valid email address',
                'email.unique' => 'Email already exists',
                'role.in' => 'Role must be admin, manager, or employee',
                'password.confirmed' => 'Password confirmation does not match'
            ]);

            // Prevent changing own role via this endpoint
            if ($user->id === auth()->id() && isset($validated['role'])) {
                return $this->errorResponse('Cannot change your own role via this endpoint', 422);
            }

            $updateData = [];

            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }

            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }

            if (isset($validated['role'])) {
                $updateData['role'] = $validated['role'];
            }

            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            return $this->updatedResponse(
                $user->only(['id', 'name', 'email', 'role', 'updated_at']),
                'User updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update user', 500, $e->getMessage());
        }
    }

    /**
     * Delete user
     * DELETE /api/admin/users/{id}
     */
    public function deleteUser(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting self
            if ($user->id === auth()->id()) {
                return $this->errorResponse('Cannot delete your own account', 422);
            }

            // Check if user has reimbursements
            $reimbursementCount = $user->reimbursements()->count();
            if ($reimbursementCount > 0) {
                return $this->errorResponse(
                    "Cannot delete user with {$reimbursementCount} reimbursements",
                    422,
                    [
                        'reimbursement_count' => $reimbursementCount,
                        'help' => 'Transfer or delete user reimbursements first'
                    ]
                );
            }

            $userName = $user->name;
            $user->delete();

            return $this->deletedResponse("User '{$userName}' deleted successfully");
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete user', 500, $e->getMessage());
        }
    }

    /**
     * Reset user password
     * POST /api/admin/users/{id}/reset-password
     */
    public function resetUserPassword(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]
            ], [
                'password.required' => 'Password is required',
                'password.confirmed' => 'Password confirmation does not match'
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);

            return $this->successResponse(
                null,
                "Password reset successfully for user '{$user->name}'"
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reset password', 500, $e->getMessage());
        }
    }
}
