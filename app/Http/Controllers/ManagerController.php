<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\Models\Reimbursement;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ManagerController extends Controller
{
    use ApiResponseTrait;
    /**
     * Get manager dashboard statistics
     * GET /api/manager/dashboard
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $stats = [
                'pending_count' => Reimbursement::where('status', 'pending')->count(),
                'approved_this_month' => Reimbursement::where('status', 'approved')
                    ->whereMonth('approved_at', $currentMonth)
                    ->whereYear('approved_at', $currentYear)
                    ->count(),
                'rejected_this_month' => Reimbursement::where('status', 'rejected')
                    ->whereMonth('updated_at', $currentMonth)
                    ->whereYear('updated_at', $currentYear)
                    ->count(),
                'total_approved_amount' => Reimbursement::where('status', 'approved')
                    ->whereMonth('approved_at', $currentMonth)
                    ->whereYear('approved_at', $currentYear)
                    ->sum('amount'),
                'average_approval_time' => $this->getAverageApprovalTime(),
                'categories_usage' => Category::withCount([
                    'reimbursements as total_count',
                    'reimbursements as pending_count' => function ($query) {
                        $query->where('status', 'pending');
                    },
                    'reimbursements as approved_count' => function ($query) use ($currentMonth, $currentYear) {
                        $query->where('status', 'approved')
                            ->whereMonth('approved_at', $currentMonth)
                            ->whereYear('approved_at', $currentYear);
                    }
                ])->get()->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'limit_type' => $category->limit_type,
                        'limit_value' => $category->limit_value,
                        'total_submissions' => $category->total_count,
                        'pending_submissions' => $category->pending_count,
                        'approved_this_month' => $category->approved_count
                    ];
                }),
                'recent_activity' => $this->getRecentActivity()
            ];

            return $this->successResponse($stats, 'Manager dashboard retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve manager dashboard', 500, $e->getMessage());
        }
    }

    /**
     * Get pending reimbursements for approval
     * GET /api/manager/pending-reimbursements
     */
    public function getPendingReimbursements(Request $request): JsonResponse
    {
        try {
            $query = Reimbursement::with(['user:id,name,email', 'category:id,name,limit_type,limit_value', 'proofs'])
                ->where('status', 'pending')
                ->orderBy('submitted_at', 'asc');

            // Apply filters
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('amount_min')) {
                $query->where('amount', '>=', $request->amount_min);
            }

            if ($request->filled('amount_max')) {
                $query->where('amount', '<=', $request->amount_max);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('submitted_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('submitted_at', '<=', $request->date_to);
            }

            // Search by title or description
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $perPage = $request->input('per_page', 15);
            $reimbursements = $query->paginate($perPage);

            return $this->successResponse($reimbursements, 'Pending reimbursements retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve pending reimbursements', 500, $e->getMessage());
        }
    }

    /**
     * Get all reimbursements with manager view
     * GET /api/manager/reimbursements
     */
    public function getAllReimbursements(Request $request): JsonResponse
    {
        try {
            $query = Reimbursement::with(['user:id,name,email', 'category:id,name', 'proofs'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            $perPage = $request->input('per_page', 20);
            $reimbursements = $query->paginate($perPage);

            return $this->successResponse($reimbursements, 'All reimbursements retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve reimbursements', 500, $e->getMessage());
        }
    }

    /**
     * Get reimbursement statistics for reporting
     * GET /api/manager/statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'month' => 'sometimes|integer|between:1,12',
                'year' => 'sometimes|integer|min:2020|max:' . (now()->year + 1),
                'category_id' => 'sometimes|exists:categories,id',
                'user_id' => 'sometimes|exists:users,id'
            ]);

            $month = $validated['month'] ?? now()->month;
            $year = $validated['year'] ?? now()->year;

            $query = Reimbursement::whereMonth('created_at', $month)
                ->whereYear('created_at', $year);

            if (isset($validated['category_id'])) {
                $query->where('category_id', $validated['category_id']);
            }

            if (isset($validated['user_id'])) {
                $query->where('user_id', $validated['user_id']);
            }

            $statistics = [
                'period' => [
                    'month' => $month,
                    'year' => $year,
                    'month_name' => now()->month($month)->format('F')
                ],
                'summary' => [
                    'total_submissions' => $query->count(),
                    'pending_count' => (clone $query)->where('status', 'pending')->count(),
                    'approved_count' => (clone $query)->where('status', 'approved')->count(),
                    'rejected_count' => (clone $query)->where('status', 'rejected')->count(),
                    'total_amount' => (clone $query)->sum('amount'),
                    'approved_amount' => (clone $query)->where('status', 'approved')->sum('amount'),
                    'average_amount' => (clone $query)->avg('amount')
                ],
                'by_category' => Category::withCount([
                    'reimbursements as total_count' => function ($q) use ($month, $year) {
                        $q->whereMonth('created_at', $month)->whereYear('created_at', $year);
                    },
                    'reimbursements as approved_count' => function ($q) use ($month, $year) {
                        $q->where('status', 'approved')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year);
                    }
                ])->get()->map(function ($category) use ($month, $year) {
                    $approved_amount = $category->reimbursements()
                        ->where('status', 'approved')
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->sum('amount');

                    return [
                        'category_name' => $category->name,
                        'total_submissions' => $category->total_count,
                        'approved_submissions' => $category->approved_count,
                        'approved_amount' => $approved_amount,
                        'limit_type' => $category->limit_type,
                        'limit_value' => $category->limit_value
                    ];
                }),
                'by_user' => User::withCount([
                    'reimbursements as total_count' => function ($q) use ($month, $year) {
                        $q->whereMonth('created_at', $month)->whereYear('created_at', $year);
                    },
                    'reimbursements as approved_count' => function ($q) use ($month, $year) {
                        $q->where('status', 'approved')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year);
                    }
                ])->having('total_count', '>', 0)
                    ->get()
                    ->map(function ($user) use ($month, $year) {
                        $approved_amount = $user->reimbursements()
                            ->where('status', 'approved')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->sum('amount');

                        return [
                            'user_name' => $user->name,
                            'user_email' => $user->email,
                            'total_submissions' => $user->total_count,
                            'approved_submissions' => $user->approved_count,
                            'approved_amount' => $approved_amount
                        ];
                    })
            ];

            return $this->successResponse($statistics, 'Statistics retrieved successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get users list for manager (for filtering purposes)
     * GET /api/manager/users
     */
    public function getUsers(): JsonResponse
    {
        try {
            $users = User::select('id', 'name', 'email', 'role')
                ->where('role', 'employee')
                ->orderBy('name')
                ->get();

            return $this->successResponse($users, 'Users list retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve users', 500, $e->getMessage());
        }
    }

    /**
     * Get user detail (Manager can only view employee details)
     * GET /api/manager/users/{id}
     */
    public function getUserDetail(string $id): JsonResponse
    {
        try {
            $currentUser = auth()->user();

            $query = User::select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at', 'updated_at')
                ->where('role', User::ROLE_EMPLOYEE)
                ->with(['reimbursements' => function ($query) {
                    $query->select('id', 'user_id', 'title', 'amount', 'status', 'created_at')
                        ->latest()
                        ->limit(10); // Latest 10 reimbursements
                }]);

            // Role-based access control
            if ($currentUser->isManager()) {
                // Manager hanya bisa lihat detail employee
                $query->where('role', User::ROLE_EMPLOYEE);
            }
            // Admin bisa lihat detail semua user (no additional filter)

            $user = $query->findOrFail($id);

            // Add user statistics
            $userStats = [
                'total_reimbursements' => $user->reimbursements()->count(),
                'pending_reimbursements' => $user->reimbursements()->where('status', 'pending')->count(),
                'approved_reimbursements' => $user->reimbursements()->where('status', 'approved')->count(),
                'rejected_reimbursements' => $user->reimbursements()->where('status', 'rejected')->count(),
                'total_approved_amount' => $user->reimbursements()->where('status', 'approved')->sum('amount'),
                'last_activity' => $user->reimbursements()->latest()->first()?->created_at,
                'current_month_submissions' => $user->reimbursements()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ];

            $user->statistics = $userStats;

            return $this->successResponse($user, 'User detail retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('User not found or access denied');
        }
    }

    /**
     * Private helper methods
     */
    private function getAverageApprovalTime(): float
    {
        $approvedReimbursements = Reimbursement::where('status', 'approved')
            ->whereNotNull('approved_at')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->get();

        if ($approvedReimbursements->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($approvedReimbursements as $reimbursement) {
            $submitTime = $reimbursement->submitted_at;
            $approveTime = $reimbursement->approved_at;
            $totalHours += $submitTime->diffInHours($approveTime);
        }

        return round($totalHours / $approvedReimbursements->count(), 2);
    }

    private function getRecentActivity(): array
    {
        return Reimbursement::with(['user:id,name', 'category:id,name'])
            ->whereIn('status', ['approved', 'rejected'])
            ->whereMonth('updated_at', now()->month)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($reimbursement) {
                return [
                    'id' => $reimbursement->id,
                    'title' => $reimbursement->title,
                    'amount' => $reimbursement->amount,
                    'status' => $reimbursement->status,
                    'user_name' => $reimbursement->user->name,
                    'category_name' => $reimbursement->category->name,
                    'updated_at' => $reimbursement->updated_at
                ];
            })->toArray();
    }
}
