<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use App\Http\Requests\ApproveRejectRequest;
use App\Models\Reimbursement;
use App\Services\ReimbursementService;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ReimbursementController extends Controller
{
    use ApiResponseTrait;
    protected $reimbursementService;
    protected $categoryService;

    public function __construct(
        ReimbursementService $reimbursementService,
        CategoryService $categoryService
    ) {
        $this->reimbursementService = $reimbursementService;
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of reimbursements
     * GET /api/reimbursements
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $filters = $request->only(['status', 'category_id', 'month', 'year']);

            if ($user->isEmployee()) {
                // Employee hanya bisa lihat reimbursement miliknya
                $reimbursements = $this->reimbursementService->getUserReimbursements($user->id, $filters);
            } else {
                // Admin/Manager bisa lihat semua
                $reimbursements = $this->reimbursementService->getAllReimbursements($filters);
            }

            return $this->successResponse($reimbursements, 'Reimbursements retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve reimbursements', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created reimbursement
     * POST /api/reimbursements
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'amount' => 'required|numeric|min:0.01',
                'category_id' => 'required|exists:categories,id',
                'proofs' => 'required|array|min:1',
                'proofs.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB max
            ]);

            // Check monthly limit sebelum create
            $limitCheck = $this->categoryService->checkMonthlyLimit(
                $validated['category_id'],
                auth()->id(),
                $validated['amount']
            );

            if (!$limitCheck['can_submit']) {
                return $this->errorResponse(
                    'Monthly limit exceeded for this category',
                    422,
                    $limitCheck
                );
            }

            // Create reimbursement
            $reimbursement = $this->reimbursementService->create(
                $validated,
                $request->file('proofs')
            );

            return $this->createdResponse($reimbursement, 'Reimbursement created successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create reimbursement', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified reimbursement
     * GET /api/reimbursements/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $reimbursement = Reimbursement::with(['category', 'user', 'proofs', 'logActivities.user'])
                ->findOrFail($id);

            return $this->successResponse($reimbursement, 'Reimbursement retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Reimbursement not found');
        }
    }

    /**
     * Update the specified reimbursement (only pending)
     * PUT /api/reimbursements/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $reimbursement = Reimbursement::findOrFail($id);

            // Validasi input
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'amount' => 'sometimes|numeric|min:0.01',
                'category_id' => 'sometimes|exists:categories,id',
                'proofs' => 'sometimes|array',
                'proofs.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
                'delete_proof_ids' => 'sometimes|array',
                'delete_proof_ids.*' => 'integer|exists:proofs,id'
            ]);

            // Check monthly limit jika amount atau category berubah
            if (isset($validated['amount']) || isset($validated['category_id'])) {
                $categoryId = $validated['category_id'] ?? $reimbursement->category_id;
                $amount = $validated['amount'] ?? $reimbursement->amount;

                $limitCheck = $this->categoryService->checkMonthlyLimit(
                    $categoryId,
                    $reimbursement->user_id,
                    $amount
                );

                if (!$limitCheck['can_submit']) {
                    return $this->errorResponse(
                        'Monthly limit exceeded for this category',
                        422,
                        $limitCheck
                    );
                }
            }

            // Update reimbursement
            $updatedReimbursement = $this->reimbursementService->update(
                $reimbursement,
                $validated,
                $request->file('proofs') ?? [],
                $validated['delete_proof_ids'] ?? []
            );

            return $this->updatedResponse($updatedReimbursement, 'Reimbursement updated successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update reimbursement', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified reimbursement (soft delete)
     * DELETE /api/reimbursements/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $reimbursement = Reimbursement::findOrFail($id);

            $this->reimbursementService->delete($reimbursement);

            return $this->deletedResponse('Reimbursement deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete reimbursement', 500, $e->getMessage());
        }
    }

    /**
     * Approve or reject reimbursement (Manager only)
     * POST /api/reimbursements/{id}/approve-reject
     */
    public function approveReject(ApproveRejectRequest $request, string $id): JsonResponse
    {
        try {
            $reimbursement = Reimbursement::findOrFail($id);
            $action = $request->validated()['action'];

            if ($action === 'approve') {
                $updatedReimbursement = $this->reimbursementService->approve(
                    $reimbursement,
                    auth()->id()
                );
                $message = 'Reimbursement approved successfully';
            } else {
                $updatedReimbursement = $this->reimbursementService->reject(
                    $reimbursement,
                    auth()->id()
                );
                $message = 'Reimbursement rejected successfully';
            }

            return $this->successResponse($updatedReimbursement, $message);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process reimbursement', 500, $e->getMessage());
        }
    }

    /**
     * Get user's monthly category usage
     * GET /api/reimbursements/category-usage
     */
    public function getCategoryUsage(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $usage = $this->categoryService->getUserCategoryUsage(
                $user->id,
                $month,
                $year
            );

            return $this->successResponse($usage, 'Category usage retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve category usage', 500, $e->getMessage());
        }
    }
}
