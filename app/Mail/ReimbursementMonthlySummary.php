<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class ReimbursementMonthlySummary extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $manager,
        public array $summaryData
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $monthYear = $this->summaryData['month_year'] ?? now()->format('F Y');

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: "📊 Monthly Reimbursement Summary - {$monthYear}",
            tags: ['reimbursement', 'summary', 'monthly', 'manager'],
            metadata: [
                'manager_id' => $this->manager->id,
                'month' => $this->summaryData['month'] ?? now()->month,
                'year' => $this->summaryData['year'] ?? now()->year,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.monthly-summary.monthly-summary',
            text: 'emails.reimbursement.monthly-summary.monthly-summary-text',
            with: [
                'manager' => $this->manager,
                'summaryData' => $this->summaryData,
                'appUrl' => config('app.url'),
                'monthYear' => $this->summaryData['month_year'] ?? now()->format('F Y'),
                'totalSubmissions' => $this->summaryData['total_submissions'] ?? 0,
                'pendingCount' => $this->summaryData['pending_count'] ?? 0,
                'approvedCount' => $this->summaryData['approved_count'] ?? 0,
                'rejectedCount' => $this->summaryData['rejected_count'] ?? 0,
                'totalAmount' => $this->formatAmount($this->summaryData['total_amount'] ?? 0),
                'approvedAmount' => $this->formatAmount($this->summaryData['approved_amount'] ?? 0),
                'averageAmount' => $this->formatAmount($this->summaryData['average_amount'] ?? 0),
                'categoryBreakdown' => $this->summaryData['category_breakdown'] ?? [],
                'topEmployees' => $this->summaryData['top_employees'] ?? [],
                'processingStats' => $this->summaryData['processing_stats'] ?? [],
            ],
        );
    }

    /**
     * Format amount to Indonesian Rupiah
     */
    private function formatAmount(float $amount): string
    {
        return 'IDR ' . number_format($amount, 0, ',', '.');
    }

    public function attachments(): array
    {
        return [];
    }
}

// ==========================================
// PENDING REMINDER EMAIL
// ==========================================

class ReimbursementPendingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $manager,
        public array $pendingReimbursements
    ) {}

    public function envelope(): Envelope
    {
        $pendingCount = count($this->pendingReimbursements);

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: "⏰ Pending Reimbursements Reminder - {$pendingCount} items awaiting approval",
            tags: ['reimbursement', 'reminder', 'pending', 'manager'],
            metadata: [
                'manager_id' => $this->manager->id,
                'pending_count' => $pendingCount,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.pending.pending-reminder',
            text: 'emails.reimbursement.pending.pending-reminder-text',
            with: [
                'manager' => $this->manager,
                'pendingReimbursements' => $this->pendingReimbursements,
                'appUrl' => config('app.url'),
                'pendingCount' => count($this->pendingReimbursements),
                'totalPendingAmount' => $this->getTotalPendingAmount(),
                'oldestPending' => $this->getOldestPending(),
                'urgentCount' => $this->getUrgentCount(),
            ],
        );
    }

    private function getTotalPendingAmount(): string
    {
        $total = array_sum(array_column($this->pendingReimbursements, 'amount'));
        return 'IDR ' . number_format($total, 0, ',', '.');
    }

    private function getOldestPending(): ?array
    {
        if (empty($this->pendingReimbursements)) {
            return null;
        }

        $oldest = null;
        $oldestDate = null;

        foreach ($this->pendingReimbursements as $reimbursement) {
            $submittedAt = \Carbon\Carbon::parse($reimbursement['submitted_at']);

            if (!$oldestDate || $submittedAt->lt($oldestDate)) {
                $oldestDate = $submittedAt;
                $oldest = $reimbursement;
                $oldest['days_pending'] = $submittedAt->diffInDays(now());
            }
        }

        return $oldest;
    }

    private function getUrgentCount(): int
    {
        $urgentCount = 0;
        $urgentThreshold = now()->subDays(3); // 3 days or older

        foreach ($this->pendingReimbursements as $reimbursement) {
            $submittedAt = \Carbon\Carbon::parse($reimbursement['submitted_at']);
            if ($submittedAt->lt($urgentThreshold)) {
                $urgentCount++;
            }
        }

        return $urgentCount;
    }

    public function attachments(): array
    {
        return [];
    }
}

// ==========================================
// LIMIT WARNING EMAIL
// ==========================================

class ReimbursementLimitWarning extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $employee,
        public array $limitData
    ) {}

    public function envelope(): Envelope
    {
        $categoryName = $this->limitData['category_name'] ?? 'Unknown';

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: "⚠️ Monthly Limit Warning - {$categoryName}",
            tags: ['reimbursement', 'warning', 'limit', 'employee'],
            metadata: [
                'employee_id' => $this->employee->id,
                'category_id' => $this->limitData['category_id'] ?? null,
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reimbursement.limit-warning.limit-warning',
            text: 'emails.reimbursement.limit-warning.limit-warning-text',
            with: [
                'employee' => $this->employee,
                'limitData' => $this->limitData,
                'appUrl' => config('app.url'),
                'categoryName' => $this->limitData['category_name'] ?? 'Unknown',
                'limitType' => $this->limitData['limit_type'] ?? 'amount',
                'limitValue' => $this->formatLimitValue(),
                'currentUsage' => $this->formatCurrentUsage(),
                'remainingLimit' => $this->formatRemainingLimit(),
                'usagePercentage' => round($this->limitData['usage_percentage'] ?? 0, 1),
                'monthYear' => now()->format('F Y'),
            ],
        );
    }

    private function formatLimitValue(): string
    {
        $limitType = $this->limitData['limit_type'] ?? 'amount';
        $limitValue = $this->limitData['limit_value'] ?? 0;

        if ($limitType === 'quota') {
            return $limitValue . ' submission(s)';
        } else {
            return 'IDR ' . number_format($limitValue, 0, ',', '.');
        }
    }

    private function formatCurrentUsage(): string
    {
        $limitType = $this->limitData['limit_type'] ?? 'amount';
        $currentUsage = $this->limitData['current_usage'] ?? 0;

        if ($limitType === 'quota') {
            return $currentUsage . ' submission(s)';
        } else {
            return 'IDR ' . number_format($currentUsage, 0, ',', '.');
        }
    }

    private function formatRemainingLimit(): string
    {
        $limitType = $this->limitData['limit_type'] ?? 'amount';
        $remaining = $this->limitData['remaining_limit'] ?? 0;

        if ($limitType === 'quota') {
            return $remaining . ' submission(s)';
        } else {
            return 'IDR ' . number_format($remaining, 0, ',', '.');
        }
    }

    public function attachments(): array
    {
        return [];
    }
}
