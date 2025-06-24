<?php

namespace App\Services;

use App\Models\LogActivities;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Auth;

class LogActivityService
{
    /**
     * Log activity for reimbursement actions
     */
    public function log(
        string $action,
        string $type,
        Reimbursement $reimbursement,
        string $oldValue = null,
        string $newValue = null,
        int $userId = null
    ): LogActivities {
        return LogActivities::create([
            'action' => $action,
            'type' => $type,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reimbursement_id' => $reimbursement->id,
            'user_id' => $userId ?? Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log new reimbursement request
     */
    public function logNewRequest(Reimbursement $reimbursement): LogActivities
    {
        return $this->log(
            "Pengajuan baru reimbursement: {$reimbursement->title}",
            LogActivities::TYPE_NEW_REQUEST,
            $reimbursement,
            null,
            json_encode([
                'title' => $reimbursement->title,
                'amount' => $reimbursement->amount,
                'category' => $reimbursement->category->name,
                'status' => $reimbursement->status
            ])
        );
    }

    /**
     * Log status change
     */
    public function logStatusChange(
        Reimbursement $reimbursement,
        string $oldStatus,
        string $newStatus,
        int $userId = null
    ): LogActivities {
        $action = "Perubahan status reimbursement dari {$oldStatus} ke {$newStatus}";

        return $this->log(
            $action,
            LogActivities::TYPE_CHANGE_STATUS,
            $reimbursement,
            $oldStatus,
            $newStatus,
            $userId
        );
    }

    /**
     * Log approval/rejection
     */
    public function logApprovalChange(
        Reimbursement $reimbursement,
        string $oldStatus,
        string $newStatus,
        int $managerId
    ): LogActivities {
        $action = $newStatus === Reimbursement::STATUS_APPROVED
            ? "Disetujui reimbursement: {$reimbursement->title}"
            : "Ditolak reimbursement: {$reimbursement->title}";

        return $this->log(
            $action,
            LogActivities::TYPE_CHANGE_APPROVAL,
            $reimbursement,
            $oldStatus,
            $newStatus,
            $managerId
        );
    }

    /**
     * Log reimbursement update
     */
    public function logUpdate(
        Reimbursement $reimbursement,
        array $oldData,
        array $newData
    ): LogActivities {
        $changes = $this->getChangedFields($oldData, $newData);
        $changesSummary = implode(', ', array_keys($changes));

        return $this->log(
            "Updated reimbursement fields: {$changesSummary}",
            LogActivities::TYPE_CHANGE_STATUS,
            $reimbursement,
            json_encode($changes['old'] ?? $oldData),
            json_encode($changes['new'] ?? $newData)
        );
    }

    /**
     * Log reimbursement deletion
     */
    public function logDeletion(Reimbursement $reimbursement): LogActivities
    {
        return $this->log(
            "Deleted reimbursement: {$reimbursement->title}",
            LogActivities::TYPE_CHANGE_STATUS,
            $reimbursement,
            $reimbursement->status,
            'deleted'
        );
    }

    /**
     * Get activity logs for specific reimbursement
     */
    public function getReimbursementLogs(int $reimbursementId, array $filters = [])
    {
        $query = LogActivities::where('reimbursement_id', $reimbursementId)
            ->with(['user', 'reimbursement']);

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest('created_at');
    }

    /**
     * Get activity logs for specific user
     */
    public function getUserLogs(int $userId, array $filters = [])
    {
        $query = LogActivities::where('user_id', $userId)
            ->with(['user', 'reimbursement.category']);

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['reimbursement_id'])) {
            $query->where('reimbursement_id', $filters['reimbursement_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest('created_at');
    }

    /**
     * Get all activity logs (admin only)
     */
    public function getAllLogs(array $filters = [])
    {
        $query = LogActivities::with(['user', 'reimbursement.category']);

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['reimbursement_id'])) {
            $query->where('reimbursement_id', $filters['reimbursement_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        return $query->latest('created_at');
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats(array $filters = []): array
    {
        $query = LogActivities::query();

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $totalActivities = $query->count();
        $newRequests = (clone $query)->where('type', LogActivities::TYPE_NEW_REQUEST)->count();
        $statusChanges = (clone $query)->where('type', LogActivities::TYPE_CHANGE_STATUS)->count();
        $approvalChanges = (clone $query)->where('type', LogActivities::TYPE_CHANGE_APPROVAL)->count();

        // Activity by type
        $activitiesByType = $query->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Top active users
        $topUsers = $query->selectRaw('user_id, COUNT(*) as activity_count')
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('activity_count')
            ->limit(10)
            ->get();

        return [
            'total_activities' => $totalActivities,
            'new_requests' => $newRequests,
            'status_changes' => $statusChanges,
            'approval_changes' => $approvalChanges,
            'activities_by_type' => $activitiesByType,
            'top_users' => $topUsers
        ];
    }

    /**
     * Get daily activity trends
     */
    public function getDailyActivityTrends(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $activities = LogActivities::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $activities->mapWithKeys(function ($item) {
            return [$item->date => $item->count];
        })->toArray();
    }

    /**
     * Clean up old logs (optional maintenance)
     */
    public function cleanupOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return LogActivities::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get changed fields between old and new data
     */
    protected function getChangedFields(array $oldData, array $newData): array
    {
        $changes = [
            'old' => [],
            'new' => []
        ];

        foreach ($newData as $key => $newValue) {
            if (isset($oldData[$key]) && $oldData[$key] != $newValue) {
                $changes['old'][$key] = $oldData[$key];
                $changes['new'][$key] = $newValue;
            }
        }

        return $changes;
    }
}
