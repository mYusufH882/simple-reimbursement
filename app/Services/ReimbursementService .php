<?php

namespace App\Services;

use App\Models\Reimbursement;
use App\Models\Proof;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReimbursementService
{
    protected $logActivityService;
    protected $fileUploadService;
    protected $emailService;

    public function __construct(
        LogActivityService $logActivityService,
        FileUploadService $fileUploadService,
        EmailService $emailService
    ) {
        $this->logActivityService = $logActivityService;
        $this->fileUploadService = $fileUploadService;
        $this->emailService = $emailService;
    }

    /**
     * Create new reimbursement
     */
    public function create(array $data, array $proofFiles): Reimbursement
    {
        return DB::transaction(function () use ($data, $proofFiles) {
            // Create reimbursement
            $reimbursement = Reimbursement::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'category_id' => $data['category_id'],
                'user_id' => auth()->id(),
                'status' => Reimbursement::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            // Upload proof files
            foreach ($proofFiles as $file) {
                $this->fileUploadService->uploadProof($file, $reimbursement->id);
            }

            // Log activity using specific method
            $this->logActivityService->logNewRequest($reimbursement);

            try {
                $this->emailService->sendReimbursementSubmitted($reimbursement);
            } catch (\Exception $e) {
                Log::error('Failed to send reimbursement submitted email', [
                    'reimbursement_id' => $reimbursement->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $reimbursement->load(['category', 'user', 'proofs']);
        });
    }

    /**
     * Update reimbursement (only pending status)
     */
    public function update(Reimbursement $reimbursement, array $data, array $proofFiles = [], array $deleteProofIds = []): Reimbursement
    {
        if (!$reimbursement->isPending()) {
            throw new \Exception('Only pending reimbursements can be updated');
        }

        return DB::transaction(function () use ($reimbursement, $data, $proofFiles, $deleteProofIds) {
            $oldData = $reimbursement->toArray();

            // Update reimbursement
            $reimbursement->update(array_filter($data));

            // Delete specified proofs
            if (!empty($deleteProofIds)) {
                $proofsToDelete = Proof::where('reimbursement_id', $reimbursement->id)
                    ->whereIn('id', $deleteProofIds)
                    ->get();

                foreach ($proofsToDelete as $proof) {
                    $this->fileUploadService->deleteProof($proof);
                }
            }

            // Upload new proof files
            foreach ($proofFiles as $file) {
                $this->fileUploadService->uploadProof($file, $reimbursement->id);
            }

            // Log activity using specific method  
            $this->logActivityService->logUpdate(
                $reimbursement,
                $oldData,
                $reimbursement->fresh()->toArray()
            );

            return $reimbursement->load(['category', 'user', 'proofs']);
        });
    }

    /**
     * Handle approve/reject action (supports ApproveRejectRequest)
     */
    public function handleApprovalAction(Reimbursement $reimbursement, string $action, int $managerId): Reimbursement
    {
        if (!$reimbursement->isPending()) {
            throw new \Exception('Only pending reimbursements can be approved or rejected');
        }

        if (!in_array($action, ['approve', 'reject'])) {
            throw new \Exception('Invalid action. Must be approve or reject');
        }

        return $action === 'approve'
            ? $this->approve($reimbursement, $managerId)
            : $this->reject($reimbursement, $managerId);
    }

    /**
     * Approve reimbursement
     */
    public function approve(Reimbursement $reimbursement, int $managerId): Reimbursement
    {
        if (!$reimbursement->isPending()) {
            throw new \Exception('Only pending reimbursements can be approved');
        }

        return DB::transaction(function () use ($reimbursement, $managerId) {
            $oldStatus = $reimbursement->status;

            $reimbursement->update([
                'status' => Reimbursement::STATUS_APPROVED,
                'approved_at' => now(),
            ]);

            // Log activity using specific method
            $this->logActivityService->logApprovalChange(
                $reimbursement,
                $oldStatus,
                Reimbursement::STATUS_APPROVED,
                $managerId
            );

            try {
                $manager = User::find($managerId);
                if ($manager) {
                    $this->emailService->sendReimbursementApproved($reimbursement, $manager);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send approval email', [
                    'reimbursement_id' => $reimbursement->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $reimbursement->load(['category', 'user', 'proofs']);
        });
    }

    /**
     * Reject reimbursement
     */
    public function reject(Reimbursement $reimbursement, int $managerId): Reimbursement
    {
        if (!$reimbursement->isPending()) {
            throw new \Exception('Only pending reimbursements can be rejected');
        }

        return DB::transaction(function () use ($reimbursement, $managerId) {
            $oldStatus = $reimbursement->status;

            $reimbursement->update([
                'status' => Reimbursement::STATUS_REJECTED,
            ]);

            // Log activity using specific method
            $this->logActivityService->logApprovalChange(
                $reimbursement,
                $oldStatus,
                Reimbursement::STATUS_REJECTED,
                $managerId
            );

            try {
                $manager = User::find($managerId);
                if ($manager) {
                    $this->emailService->sendReimbursementRejected($reimbursement, $manager);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send rejection email', [
                    'reimbursement_id' => $reimbursement->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $reimbursement->load(['category', 'user', 'proofs']);
        });
    }

    /**
     * Soft delete reimbursement
     */
    public function delete(Reimbursement $reimbursement): bool
    {
        if (!$reimbursement->isPending()) {
            throw new \Exception('Only pending reimbursements can be deleted');
        }

        return DB::transaction(function () use ($reimbursement) {
            // Log activity before deletion using specific method
            $this->logActivityService->logDeletion($reimbursement);

            return $reimbursement->delete();
        });
    }

    /**
     * Get user's reimbursements with filters
     */
    public function getUserReimbursements(int $userId, array $filters = [])
    {
        $query = Reimbursement::where('user_id', $userId)
            ->with(['category', 'proofs', 'logActivities.user']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['month'])) {
            $query->whereMonth('created_at', $filters['month']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        return $query->latest('created_at');
    }

    /**
     * Get all reimbursements for admin/manager
     */
    public function getAllReimbursements(array $filters = [])
    {
        $query = Reimbursement::with(['category', 'user', 'proofs', 'logActivities.user']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['month'])) {
            $query->whereMonth('created_at', $filters['month']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        return $query->latest('created_at');
    }

    /**
     * Get deleted reimbursements (admin only)
     */
    public function getDeletedReimbursements()
    {
        return Reimbursement::onlyTrashed()
            ->with(['category', 'user', 'proofs', 'logActivities.user'])
            ->latest('deleted_at');
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStatistics(int $userId = null, int $month = null, int $year = null): array
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $query = Reimbursement::whereMonth('created_at', $month)
            ->whereYear('created_at', $year);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $totalSubmitted = $query->count();
        $totalApproved = $query->where('status', Reimbursement::STATUS_APPROVED)->count();
        $totalRejected = $query->where('status', Reimbursement::STATUS_REJECTED)->count();
        $totalPending = $query->where('status', Reimbursement::STATUS_PENDING)->count();
        $totalAmount = $query->where('status', Reimbursement::STATUS_APPROVED)->sum('amount');

        return [
            'month' => $month,
            'year' => $year,
            'total_submitted' => $totalSubmitted,
            'total_approved' => $totalApproved,
            'total_rejected' => $totalRejected,
            'total_pending' => $totalPending,
            'total_amount' => $totalAmount,
        ];
    }
}
