<?php

namespace App\Services;

use App\Models\Reimbursement;
use App\Models\User;
use App\Mail\ReimbursementSubmitted;
use App\Mail\ReimbursementApproved;
use App\Mail\ReimbursementRejected;
use App\Mail\ReimbursementStatusChanged;
use App\Jobs\SendReimbursementEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send email notification for new reimbursement submission
     */
    public function sendReimbursementSubmitted(Reimbursement $reimbursement): bool
    {
        try {
            // Get managers to notify
            $managers = User::where('role', User::ROLE_MANAGER)
                ->orWhere('role', User::ROLE_ADMIN)
                ->get();

            if ($managers->isEmpty()) {
                Log::warning('No managers found to notify for reimbursement submission', [
                    'reimbursement_id' => $reimbursement->id
                ]);
                return false;
            }

            // Send email to all managers via queue
            foreach ($managers as $manager) {
                SendReimbursementEmail::dispatch(
                    $manager,
                    'submitted',
                    $reimbursement
                )->delay(now()->addSeconds(5));
            }

            // Send confirmation email to employee
            SendReimbursementEmail::dispatch(
                $reimbursement->user,
                'submitted_confirmation',
                $reimbursement
            )->delay(now()->addSeconds(2));

            Log::info('Reimbursement submission emails queued successfully', [
                'reimbursement_id' => $reimbursement->id,
                'managers_count' => $managers->count()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue reimbursement submission emails', [
                'reimbursement_id' => $reimbursement->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send email notification for reimbursement approval
     */
    public function sendReimbursementApproved(Reimbursement $reimbursement, User $approver): bool
    {
        try {
            // Send approval email to employee
            SendReimbursementEmail::dispatch(
                $reimbursement->user,
                'approved',
                $reimbursement,
                ['approver' => $approver]
            )->delay(now()->addSeconds(3));

            // Send notification to admin (for tracking)
            $admins = User::where('role', User::ROLE_ADMIN)->get();
            foreach ($admins as $admin) {
                SendReimbursementEmail::dispatch(
                    $admin,
                    'approved_notification',
                    $reimbursement,
                    ['approver' => $approver]
                )->delay(now()->addSeconds(8));
            }

            Log::info('Reimbursement approval emails queued successfully', [
                'reimbursement_id' => $reimbursement->id,
                'approver_id' => $approver->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue reimbursement approval emails', [
                'reimbursement_id' => $reimbursement->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send email notification for reimbursement rejection
     */
    public function sendReimbursementRejected(Reimbursement $reimbursement, User $rejector, string $reason = null): bool
    {
        try {
            // Send rejection email to employee
            SendReimbursementEmail::dispatch(
                $reimbursement->user,
                'rejected',
                $reimbursement,
                [
                    'rejector' => $rejector,
                    'reason' => $reason
                ]
            )->delay(now()->addSeconds(3));

            // Send notification to admin (for tracking)
            $admins = User::where('role', User::ROLE_ADMIN)->get();
            foreach ($admins as $admin) {
                SendReimbursementEmail::dispatch(
                    $admin,
                    'rejected_notification',
                    $reimbursement,
                    [
                        'rejector' => $rejector,
                        'reason' => $reason
                    ]
                )->delay(now()->addSeconds(8));
            }

            Log::info('Reimbursement rejection emails queued successfully', [
                'reimbursement_id' => $reimbursement->id,
                'rejector_id' => $rejector->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue reimbursement rejection emails', [
                'reimbursement_id' => $reimbursement->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send monthly summary email to managers
     */
    public function sendMonthlySummary(User $manager, array $summaryData): bool
    {
        try {
            SendReimbursementEmail::dispatch(
                $manager,
                'monthly_summary',
                null,
                $summaryData
            )->delay(now()->addMinutes(2));

            Log::info('Monthly summary email queued successfully', [
                'manager_id' => $manager->id,
                'month' => $summaryData['month'] ?? 'unknown'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue monthly summary email', [
                'manager_id' => $manager->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send reminder email for pending reimbursements
     */
    public function sendPendingReminder(User $manager, array $pendingReimbursements): bool
    {
        try {
            if (empty($pendingReimbursements)) {
                return true; // No pending reimbursements, nothing to send
            }

            SendReimbursementEmail::dispatch(
                $manager,
                'pending_reminder',
                null,
                ['pending_reimbursements' => $pendingReimbursements]
            )->delay(now()->addMinutes(1));

            Log::info('Pending reminder email queued successfully', [
                'manager_id' => $manager->id,
                'pending_count' => count($pendingReimbursements)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue pending reminder email', [
                'manager_id' => $manager->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send limit warning email to employee
     */
    public function sendLimitWarning(User $employee, array $limitData): bool
    {
        try {
            SendReimbursementEmail::dispatch(
                $employee,
                'limit_warning',
                null,
                $limitData
            )->delay(now()->addSeconds(5));

            Log::info('Limit warning email queued successfully', [
                'employee_id' => $employee->id,
                'category' => $limitData['category_name'] ?? 'unknown'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue limit warning email', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration(): array
    {
        try {
            // Try to get mail configuration
            $driver = config('mail.default');
            $host = config("mail.mailers.{$driver}.host");
            $port = config("mail.mailers.{$driver}.port");
            $from = config('mail.from.address');

            return [
                'status' => 'success',
                'message' => 'Email configuration looks good',
                'config' => [
                    'driver' => $driver,
                    'host' => $host,
                    'port' => $port,
                    'from' => $from,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Email configuration error: ' . $e->getMessage(),
                'config' => null
            ];
        }
    }

    /**
     * Send test email
     */
    public function sendTestEmail(string $to, string $subject = 'Test Email'): bool
    {
        try {
            Mail::raw('This is a test email from Reimbursement System.', function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject);
            });

            Log::info('Test email sent successfully', ['to' => $to]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
