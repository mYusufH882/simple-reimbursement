<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * Create new category
     */
    public function create(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'limit_type' => $data['limit_type'],
            'limit_value' => $data['limit_value'],
        ]);
    }

    /**
     * Update category
     */
    public function update(Category $category, array $data): Category
    {
        // Data integrity check sudah dilakukan di UpdateCategoryRequest
        $category->update(array_filter($data));

        return $category->fresh();
    }

    /**
     * Delete category (only if no reimbursements)
     */
    public function delete(Category $category): bool
    {
        if ($category->reimbursements()->count() > 0) {
            throw new \Exception('Cannot delete category that has existing reimbursements');
        }

        return $category->delete();
    }

    /**
     * Get all categories with usage statistics
     */
    public function getAllWithStats(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::withCount([
            'reimbursements',
            'reimbursements as pending_count' => function ($query) {
                $query->where('status', Reimbursement::STATUS_PENDING);
            },
            'reimbursements as approved_count' => function ($query) {
                $query->where('status', Reimbursement::STATUS_APPROVED);
            },
            'reimbursements as rejected_count' => function ($query) {
                $query->where('status', Reimbursement::STATUS_REJECTED);
            }
        ])->get();
    }

    /**
     * Check monthly limit for user in specific category
     */
    public function checkMonthlyLimit(
        int $categoryId,
        int $userId,
        float $amount = 0,
        int $month = null,
        int $year = null
    ): array {
        $category = Category::findOrFail($categoryId);
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $approvedReimbursements = Reimbursement::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('status', Reimbursement::STATUS_APPROVED)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        if ($category->isQuotaType()) {
            $currentCount = $approvedReimbursements->count();
            $remaining = $category->limit_value - $currentCount;

            return [
                'type' => 'quota',
                'limit' => $category->limit_value,
                'used' => $currentCount,
                'remaining' => max(0, $remaining),
                'can_submit' => $remaining > 0,
                'percentage_used' => $category->limit_value > 0 ? ($currentCount / $category->limit_value) * 100 : 0
            ];
        } else {
            $currentTotal = $approvedReimbursements->sum('amount');
            $projectedTotal = $currentTotal + $amount;
            $remaining = $category->limit_value - $currentTotal;

            return [
                'type' => 'amount',
                'limit' => $category->limit_value,
                'used' => $currentTotal,
                'remaining' => max(0, $remaining),
                'can_submit' => $projectedTotal <= $category->limit_value,
                'percentage_used' => $category->limit_value > 0 ? ($currentTotal / $category->limit_value) * 100 : 0,
                'projected_total' => $projectedTotal
            ];
        }
    }

    /**
     * Get category usage summary for specific user
     */
    public function getUserCategoryUsage(int $userId, int $month = null, int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $categories = Category::all();
        $usage = [];

        foreach ($categories as $category) {
            $limitCheck = $this->checkMonthlyLimit($category->id, $userId, 0, $month, $year);

            $usage[] = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'limit_type' => $category->limit_type,
                'limit_value' => $category->limit_value,
                'usage' => $limitCheck
            ];
        }

        return $usage;
    }

    /**
     * Get category statistics for admin/manager
     */
    public function getCategoryStatistics(int $categoryId = null, int $month = null, int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $query = Category::query();

        if ($categoryId) {
            $query->where('id', $categoryId);
        }

        $categories = $query->get();
        $statistics = [];

        foreach ($categories as $category) {
            $reimbursements = $category->reimbursements()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);

            $totalSubmissions = $reimbursements->count();
            $approvedSubmissions = $reimbursements->where('status', Reimbursement::STATUS_APPROVED)->count();
            $rejectedSubmissions = $reimbursements->where('status', Reimbursement::STATUS_REJECTED)->count();
            $pendingSubmissions = $reimbursements->where('status', Reimbursement::STATUS_PENDING)->count();

            $totalAmount = $reimbursements->where('status', Reimbursement::STATUS_APPROVED)->sum('amount');

            // Calculate usage percentage
            if ($category->isQuotaType()) {
                $usagePercentage = $category->limit_value > 0 ? ($approvedSubmissions / $category->limit_value) * 100 : 0;
            } else {
                $usagePercentage = $category->limit_value > 0 ? ($totalAmount / $category->limit_value) * 100 : 0;
            }

            $statistics[] = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'limit_type' => $category->limit_type,
                'limit_value' => $category->limit_value,
                'total_submissions' => $totalSubmissions,
                'approved_submissions' => $approvedSubmissions,
                'rejected_submissions' => $rejectedSubmissions,
                'pending_submissions' => $pendingSubmissions,
                'total_amount' => $totalAmount,
                'usage_percentage' => round($usagePercentage, 2),
                'approval_rate' => $totalSubmissions > 0 ? round(($approvedSubmissions / $totalSubmissions) * 100, 2) : 0
            ];
        }

        return $statistics;
    }

    /**
     * Get top categories by usage
     */
    public function getTopCategoriesByUsage(int $limit = 10, int $month = null, int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $categories = Category::withCount([
            'reimbursements as total_submissions' => function ($query) use ($month, $year) {
                $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
            },
            'reimbursements as approved_submissions' => function ($query) use ($month, $year) {
                $query->where('status', Reimbursement::STATUS_APPROVED)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
            }
        ])->orderByDesc('total_submissions')
            ->limit($limit)
            ->get();

        return $categories->map(function ($category) use ($month, $year) {
            $totalAmount = $category->reimbursements()
                ->where('status', Reimbursement::STATUS_APPROVED)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('amount');

            return [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'limit_type' => $category->limit_type,
                'limit_value' => $category->limit_value,
                'total_submissions' => $category->total_submissions,
                'approved_submissions' => $category->approved_submissions,
                'total_amount' => $totalAmount
            ];
        })->toArray();
    }

    /**
     * Validate category limits before reimbursement submission
     */
    public function validateSubmission(int $categoryId, int $userId, float $amount): array
    {
        $limitCheck = $this->checkMonthlyLimit($categoryId, $userId, $amount);

        if (!$limitCheck['can_submit']) {
            $category = Category::find($categoryId);

            if ($category->isQuotaType()) {
                throw new \Exception("Quota limit exceeded. You have reached the maximum of {$category->limit_value} submissions for {$category->name} this month.");
            } else {
                $remaining = number_format($limitCheck['remaining'], 2);
                throw new \Exception("Amount limit exceeded. You have {$remaining} remaining budget for {$category->name} this month.");
            }
        }

        return $limitCheck;
    }

    /**
     * Get available categories for user (those not exceeding limits)
     */
    public function getAvailableCategories(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $categories = Category::all();

        return $categories->filter(function ($category) use ($userId) {
            $limitCheck = $this->checkMonthlyLimit($category->id, $userId);
            return $limitCheck['can_submit'];
        });
    }

    /**
     * Bulk update category limits
     */
    public function bulkUpdateLimits(array $updates): array
    {
        $results = [];

        DB::transaction(function () use ($updates, &$results) {
            foreach ($updates as $update) {
                $category = Category::findOrFail($update['category_id']);

                // Check if category has reimbursements and trying to change type
                if (
                    isset($update['limit_type']) &&
                    $category->limit_type !== $update['limit_type'] &&
                    $category->reimbursements()->count() > 0
                ) {

                    $results[] = [
                        'category_id' => $category->id,
                        'success' => false,
                        'error' => 'Cannot change limit type when category has existing reimbursements'
                    ];
                    continue;
                }

                $category->update(array_filter($update, function ($key) {
                    return in_array($key, ['limit_type', 'limit_value']);
                }, ARRAY_FILTER_USE_KEY));

                $results[] = [
                    'category_id' => $category->id,
                    'success' => true,
                    'updated_fields' => array_keys(array_filter($update))
                ];
            }
        });

        return $results;
    }
}
