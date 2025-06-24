<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories
     * GET /api/categories
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->isAdmin() || $user->isManager()) {
                // Admin/Manager bisa lihat dengan statistics
                $withStats = $request->boolean('with_stats', false);

                if ($withStats) {
                    $categories = $this->categoryService->getAllWithStats();
                } else {
                    $categories = Category::all();
                }
            } else {
                // Employee hanya lihat basic info
                $categories = Category::select('id', 'name', 'limit_type', 'limit_value')->get();
            }

            return $this->successResponse($categories, 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve categories', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created category (Admin only)
     * POST /api/categories
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'limit_type' => 'required|in:quota,amount',
                'limit_value' => 'required|numeric|min:1'
            ], [
                'name.required' => 'Category name is required',
                'name.unique' => 'Category name already exists',
                'limit_type.required' => 'Limit type is required',
                'limit_type.in' => 'Limit type must be either quota or amount',
                'limit_value.required' => 'Limit value is required',
                'limit_value.min' => 'Limit value must be at least 1'
            ]);

            $category = $this->categoryService->create($validated);

            return $this->createdResponse($category, 'Category created successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create category', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified category
     * GET /api/categories/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->isAdmin() || $user->isManager()) {
                // Admin/Manager bisa lihat detail dengan stats
                $category = Category::withCount([
                    'reimbursements',
                    'reimbursements as pending_count' => function ($query) {
                        $query->where('status', 'pending');
                    },
                    'reimbursements as approved_count' => function ($query) {
                        $query->where('status', 'approved');
                    },
                    'reimbursements as rejected_count' => function ($query) {
                        $query->where('status', 'rejected');
                    }
                ])->findOrFail($id);
            } else {
                // Employee hanya basic info
                $category = Category::select('id', 'name', 'limit_type', 'limit_value')
                    ->findOrFail($id);
            }

            return $this->successResponse($category, 'Category retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Category not found');
        }
    }

    /**
     * Update the specified category (Admin only)
     * PUT /api/categories/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            // Validasi input
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
                'limit_type' => 'sometimes|in:quota,amount',
                'limit_value' => 'sometimes|numeric|min:1'
            ], [
                'name.unique' => 'Category name already exists',
                'limit_type.in' => 'Limit type must be either quota or amount',
                'limit_value.min' => 'Limit value must be at least 1'
            ]);

            $updatedCategory = $this->categoryService->update($category, $validated);

            return $this->updatedResponse($updatedCategory, 'Category updated successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update category', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified category (Admin only)
     * DELETE /api/categories/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            $this->categoryService->delete($category);

            return $this->deletedResponse('Category deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete category', 500, $e->getMessage());
        }
    }

    /**
     * Get category statistics (Admin/Manager only)
     * GET /api/categories/{id}/statistics
     */
    public function getStatistics(Request $request, string $id): JsonResponse
    {
        try {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $request->validate([
                'month' => 'sometimes|integer|between:1,12',
                'year' => 'sometimes|integer|min:2020|max:' . (now()->year + 1)
            ]);

            $statistics = $this->categoryService->getCategoryStatistics(
                (int) $id,
                $month,
                $year
            );

            return $this->successResponse($statistics, 'Category statistics retrieved successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve statistics', 500, $e->getMessage());
        }
    }

    /**
     * Get all categories statistics (Admin/Manager only)  
     * GET /api/categories/statistics
     */
    public function getAllStatistics(Request $request): JsonResponse
    {
        try {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $request->validate([
                'month' => 'sometimes|integer|between:1,12',
                'year' => 'sometimes|integer|min:2020|max:' . (now()->year + 1)
            ]);

            $statistics = $this->categoryService->getCategoryStatistics(
                null, // All categories
                $month,
                $year
            );

            return $this->successResponse($statistics, 'All categories statistics retrieved successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve statistics', 500, $e->getMessage());
        }
    }

    /**
     * Check monthly limit for specific category and user
     * GET /api/categories/{id}/check-limit
     */
    public function checkLimit(Request $request, string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);
            $amount = $request->input('amount', 0);

            $request->validate([
                'month' => 'sometimes|integer|between:1,12',
                'year' => 'sometimes|integer|min:2020|max:' . (now()->year + 1),
                'amount' => 'sometimes|numeric|min:0'
            ]);

            $limitCheck = $this->categoryService->checkMonthlyLimit(
                (int) $id,
                $user->id,
                (float) $amount,
                $month,
                $year
            );

            return $this->successResponse($limitCheck, 'Limit check completed successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check limit', 500, $e->getMessage());
        }
    }
}
