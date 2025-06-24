<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Reimbursement;
use App\Mail\ReimbursementSubmitted;
use App\Mail\ReimbursementApproved;
use App\Mail\ReimbursementRejected;
use App\Mail\ReimbursementMonthlySummary;
use App\Mail\ReimbursementPendingReminder;
use App\Mail\ReimbursementLimitWarning;
use App\Mail\ReimbursementSubmittedConfirmation;
use App\Mail\ReimbursementApprovedNotification;
use App\Mail\ReimbursementRejectedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendReimbursementEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Retry 3 times if failed
    public $timeout = 30; // 30 seconds timeout
    public $backoff = [10, 30, 60]; // Retry after 10s, 30s, 60s

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $emailType,
        public ?Reimbursement $reimbursement = null,
        public array $additionalData = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $mailable = $this->createMailable();

            if ($mailable) {
                Mail::to($this->user->email)->send($mailable);

                Log::info('Email sent successfully', [
                    'user_id' => $this->user->id,
                    'email_type' => $this->emailType,
                    'reimbursement_id' => $this->reimbursement?->id,
                    'attempt' => $this->attempts()
                ]);
            } else {
                throw new \Exception("Unknown email type: {$this->emailType}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'user_id' => $this->user->id,
                'email_type' => $this->emailType,
                'reimbursement_id' => $this->reimbursement?->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Create the appropriate mailable based on email type
     */
    private function createMailable()
    {
        return match ($this->emailType) {
            // Employee notifications
            'submitted_confirmation' => new ReimbursementSubmittedConfirmation(
                $this->reimbursement,
                $this->user
            ),
            'approved' => new ReimbursementApproved(
                $this->reimbursement,
                $this->user,
                $this->additionalData['approver'] ?? null
            ),
            'rejected' => new ReimbursementRejected(
                $this->reimbursement,
                $this->user,
                $this->additionalData['rejector'] ?? null,
                $this->additionalData['reason'] ?? null
            ),
            'limit_warning' => new ReimbursementLimitWarning(
                $this->user,
                $this->additionalData
            ),

            // Manager notifications
            'submitted' => new ReimbursementSubmitted(
                $this->reimbursement,
                $this->user
            ),
            'monthly_summary' => new ReimbursementMonthlySummary(
                $this->user,
                $this->additionalData
            ),
            'pending_reminder' => new ReimbursementPendingReminder(
                $this->user,
                $this->additionalData['pending_reimbursements'] ?? []
            ),

            // Admin notifications
            'approved_notification' => new ReimbursementApprovedNotification(
                $this->reimbursement,
                $this->user,
                $this->additionalData['approver'] ?? null
            ),
            'rejected_notification' => new ReimbursementRejectedNotification(
                $this->reimbursement,
                $this->user,
                $this->additionalData['rejector'] ?? null,
                $this->additionalData['reason'] ?? null
            ),

            default => null
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Email job failed permanently', [
            'user_id' => $this->user->id,
            'email_type' => $this->emailType,
            'reimbursement_id' => $this->reimbursement?->id,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Optional: Send notification to admin about failed email
        // You could implement a fallback notification system here
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10); // Give up after 10 minutes
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'email',
            'reimbursement',
            $this->emailType,
            "user:{$this->user->id}",
            $this->reimbursement ? "reimbursement:{$this->reimbursement->id}" : 'no-reimbursement'
        ];
    }
}
